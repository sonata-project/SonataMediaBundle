<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Command;

use Sonata\MediaBundle\Command\CleanMediaCommand;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class CleanMediaCommandTest extends FilesystemTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ContainerInterface
     */
    protected $container;

    /**
     * @var Application
     */
    protected $application;

    /**
     * @var ContainerAwareCommand
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $tester;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Pool
     */
    private $pool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Local
     */
    private $fileSystemLocal;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->command = new CleanMediaCommand();
        $this->command->setContainer($this->container);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:clean-uploads'));

        $this->pool = $pool = $this->createMock(Pool::class);

        $this->mediaManager = $mediaManager = $this->createMock(MediaManagerInterface::class);

        $this->fileSystemLocal = $fileSystemLocal = $this->createMock(Local::class);
        $this->fileSystemLocal->expects($this->once())->method('getDirectory')->will($this->returnValue($this->workspace));

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($id) use ($pool, $mediaManager, $fileSystemLocal) {
                switch ($id) {
                    case 'sonata.media.pool':
                        return $pool;
                    case 'sonata.media.manager.media':
                        return $mediaManager;
                    case 'sonata.media.adapter.filesystem.local':
                        return $fileSystemLocal;
                }
            }));
    }

    public function testExecuteDirectoryNotExists(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->expects($this->once())->method('getContexts')->will($this->returnValue(['foo' => $context]));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@\'.+\' does not exist\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteEmptyDirectory(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->expects($this->once())->method('getContexts')->will($this->returnValue(['foo' => $context]));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@Context: foo\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteFilesExists(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $provider = $this->createMock(FileProvider::class);
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue([$provider]));

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->will($this->returnValue([$media]));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->will($this->returnValue([$media]));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertRegExp('@Context: foo\s+done!@', $this->tester->getDisplay());

        $this->assertSame(0, $output);
    }

    public function testExecuteFilesExistsVerbose(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $provider = $this->createMock(FileProvider::class);
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue([$provider]));

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->will($this->returnValue([$media]));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->will($this->returnValue([$media]));

        $output = $this->tester->execute(
            ['command' => $this->command->getName()],
            ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]
        );

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            [
                '\'qwertz.ext\' found',
                '\'thumb_1_bar.ext\' found',
            ],
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    public function testExecuteDryRun(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $provider = $this->createMock(FileProvider::class);
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue([$provider]));

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->will($this->returnValue([]));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->will($this->returnValue([]));

        $output = $this->tester->execute(['command' => $this->command->getName(), '--dry-run' => true]);

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            [
                '\'qwertz.ext\' is orphanend',
                '\'thumb_1_bar.ext\' is orphanend',
            ],
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    public function testExecute(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $provider = $this->createMock(FileProvider::class);
        $provider->expects($this->any())->method('getName')->will($this->returnValue('fooprovider'));

        $this->pool->expects($this->any())->method('getContexts')->will($this->returnValue(['foo' => $context]));
        $this->pool->expects($this->any())->method('getProviders')->will($this->returnValue([$provider]));

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->will($this->returnValue([]));
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->will($this->returnValue([]));

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertOutputFoundInContext(
            '/Context: foo\s+(.+)\s+done!/ms',
            [
                '\'qwertz.ext\' was successfully removed',
                '\'thumb_1_bar.ext\' was successfully removed',
            ],
            $this->tester->getDisplay()
        );
        $this->assertSame(0, $output);
    }

    /**
     * Asserts whether all expected texts can be found in the output within a given context.
     *
     * @param string $extract  PCRE regex expected to have a single matching group, extracting the content of a context
     * @param array  $expected Excerpts of text expected to be found in the output
     * @param string $output   Searched output
     */
    private function assertOutputFoundInContext($extractor, $expected, $output): void
    {
        preg_match_all($extractor, $output, $matches);

        $found = false;
        foreach ($matches[1] as $match) {
            if ($this->containsAll($match, $expected)) {
                $found = true;

                break;
            }
        }

        $this->assertTrue($found, sprintf(
            'Unable to find "%s" in "%s" with extractor "%s"',
            implode('", "', $expected),
            $output,
            $extractor
        ));
    }

    /**
     * Returns whether every needle can be found as a substring of the haystack.
     *
     * @param string $haystack
     * @param array  $needles  Array of (potential) substrings of the haystack
     */
    private function containsAll($haystack, $needles)
    {
        foreach ($needles as $needle) {
            if (false === strpos($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }
}
