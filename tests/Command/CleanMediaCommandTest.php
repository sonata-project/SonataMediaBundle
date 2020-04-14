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
use Sonata\MediaBundle\Tests\Fixtures\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class CleanMediaCommandTest extends FilesystemTestCase
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var CommandTester
     */
    protected $tester;

    private $pool;

    private $mediaManager;

    private $fileSystemLocal;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = $pool = $this->createMock(Pool::class);

        $this->mediaManager = $mediaManager = $this->createMock(MediaManagerInterface::class);

        $this->fileSystemLocal = $fileSystemLocal = $this->createMock(Local::class);
        $this->fileSystemLocal->expects($this->once())->method('getDirectory')->willReturn($this->workspace);

        $this->command = new CleanMediaCommand($this->fileSystemLocal, $this->pool, $this->mediaManager);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:clean-uploads'));
    }

    public function testExecuteDirectoryNotExists(): void
    {
        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $this->pool->expects($this->once())->method('getContexts')->willReturn(['foo' => $context]);

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

        $this->pool->expects($this->once())->method('getContexts')->willReturn(['foo' => $context]);

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
        $provider->method('getName')->willReturn('fooprovider');

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);
        $this->pool->method('getProviders')->willReturn([$provider]);

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->willReturn([$media]);
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->willReturn([$media]);

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
        $provider->method('getName')->willReturn('fooprovider');

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);
        $this->pool->method('getProviders')->willReturn([$provider]);

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->willReturn([$media]);
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->willReturn([$media]);

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
        $provider->method('getName')->willReturn('fooprovider');

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);
        $this->pool->method('getProviders')->willReturn([$provider]);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->willReturn(null);
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->willReturn([]);

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
        $provider->method('getName')->willReturn('fooprovider');

        $this->pool->method('getContexts')->willReturn(['foo' => $context]);
        $this->pool->method('getProviders')->willReturn([$provider]);

        $this->mediaManager->expects($this->once())->method('findOneBy')
            ->with($this->equalTo(['id' => 1, 'context' => 'foo']))
            ->willReturn(null);
        $this->mediaManager->expects($this->once())->method('findBy')
            ->with($this->equalTo(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']]))
            ->willReturn([]);

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
     */
    private function assertOutputFoundInContext(
        string $extractor,
        array $expected,
        string $output
    ): void {
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
     */
    private function containsAll(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (false === strpos($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }
}
