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

use Gaufrette\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Command\CleanMediaCommand;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\Fixtures\FilesystemTestCase;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class CleanMediaCommandTest extends FilesystemTestCase
{
    private Application $application;

    private Command $command;

    private CommandTester $tester;

    private Pool $pool;

    /**
     * @var MockObject&MediaManagerInterface
     */
    private MockObject $mediaManager;

    private FileProvider $provider;

    private Local $fileSystemLocal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool('default');
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->fileSystemLocal = new Local($this->workspace);
        $this->provider = new FileProvider(
            'fooprovider',
            $this->createStub(Filesystem::class),
            $this->createStub(CDNInterface::class),
            $this->createStub(GeneratorInterface::class),
            $this->createStub(ThumbnailInterface::class)
        );

        $this->command = new CleanMediaCommand($this->fileSystemLocal, $this->pool, $this->mediaManager);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:clean-uploads'));
    }

    public function testExecuteDirectoryNotExists(): void
    {
        $this->pool->addContext('foo');

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@\'.+\' does not exist\s+done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteEmptyDirectory(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');

        $this->pool->addContext('foo');

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@Context: foo\s+done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteFilesExists(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $this->pool->addContext('foo');
        $this->pool->addProvider('provider', $this->provider);

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects(static::once())->method('findOneBy')
            ->with(['id' => 1, 'context' => 'foo'])
            ->willReturn($media);
        $this->mediaManager->expects(static::once())->method('findBy')
            ->with(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']])
            ->willReturn([$media]);

        $output = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertMatchesRegularExpression('@Context: foo\s+done!@', $this->tester->getDisplay());

        static::assertSame(0, $output);
    }

    public function testExecuteFilesExistsVerbose(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $this->pool->addContext('foo');
        $this->pool->addProvider('provider', $this->provider);

        $media = $this->createMock(MediaInterface::class);

        $this->mediaManager->expects(static::once())->method('findOneBy')
            ->with(['id' => 1, 'context' => 'foo'])
            ->willReturn($media);
        $this->mediaManager->expects(static::once())->method('findBy')
            ->with(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']])
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
        static::assertSame(0, $output);
    }

    public function testExecuteDryRun(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $this->pool->addContext('foo');
        $this->pool->addProvider('provider', $this->provider);

        $this->mediaManager->expects(static::once())->method('findOneBy')
            ->with(['id' => 1, 'context' => 'foo'])
            ->willReturn(null);
        $this->mediaManager->expects(static::once())->method('findBy')
            ->with(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']])
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
        static::assertSame(0, $output);
    }

    public function testExecute(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'qwertz.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_bar.ext');

        $this->pool->addContext('foo');
        $this->pool->addProvider('provider', $this->provider);

        $this->mediaManager->expects(static::once())->method('findOneBy')
            ->with(['id' => 1, 'context' => 'foo'])
            ->willReturn(null);
        $this->mediaManager->expects(static::once())->method('findBy')
            ->with(['providerReference' => 'qwertz.ext', 'providerName' => ['fooprovider']])
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
        static::assertSame(0, $output);
    }

    /**
     * Asserts whether all expected texts can be found in the output within a given context.
     *
     * @param non-empty-string $extractor
     * @param string[]         $expected
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

        static::assertTrue($found, \sprintf(
            'Unable to find "%s" in "%s" with extractor "%s"',
            implode('", "', $expected),
            $output,
            $extractor
        ));
    }

    /**
     * Returns whether every needle can be found as a substring of the haystack.
     *
     * @param string[] $needles
     */
    private function containsAll(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (!str_contains($haystack, $needle)) {
                return false;
            }
        }

        return true;
    }
}
