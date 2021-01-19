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
use Sonata\MediaBundle\Command\RemoveThumbsCommand;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Tests\Fixtures\FilesystemTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\Container;

/**
 * @author Anton Dyshkant <vyshkant@gmail.com>
 *
 * @requires function Symfony\Component\Console\Tester\CommandTester::setInputs
 */
final class RemoveThumbsCommandTest extends FilesystemTestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var RemoveThumbsCommand
     */
    private $command;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var Pool|MockObject
     */
    private $pool;

    /**
     * @var MediaManagerInterface|MockObject
     */
    private $mediaManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaManager = $this->createStub(MediaManagerInterface::class);
        $this->pool = $this->createStub(Pool::class);

        $this->command = new RemoveThumbsCommand($this->mediaManager, $this->pool);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:remove-thumbnails'));
    }

    public function testExecuteWithoutArguments(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_foo.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_2_bar.ext');

        $context = [
            'providers' => [],
            'formats' => [],
            'download' => [],
        ];

        $formats = [
            'small' => [],
        ];

        $fileProvider = $this->createMock(FileProvider::class);

        $fileProvider
            ->method('getName')
            ->willReturn('fooprovider');
        $fileProvider->expects($this->once())
            ->method('getFormats')
            ->willReturn($formats);
        $fileProvider->expects($this->exactly(2))
            ->method('removeThumbnails');
        $fileProvider->expects($this->exactly(2))
            ->method('getFilesystem')
            ->willReturn($this->createMock(Filesystem::class));

        $this->pool->expects($this->once())
            ->method('getContexts')
            ->willReturn(['foo' => $context]);
        $this->pool->expects($this->once())
            ->method('getProviders')
            ->willReturn(['fooprovider' => $fileProvider]);
        $this->pool->expects($this->once())
            ->method('getProvider')
            ->willReturn($fileProvider);

        $medias = [];

        $media = new Media();
        $media->setId(1);
        $media->setName('foo');
        $medias[] = $media;

        $media = new Media();
        $media->setId(2);
        $media->setName('bar');
        $medias[] = $media;

        $findByReturnCallback = static function (
            array $criteria,
            ?array $orderBy = null,
            $limit = null,
            $offset = null
        ) use ($medias) {
            if (null !== $offset && $offset > 0) {
                return [];
            }

            return $medias;
        };

        $this->mediaManager->expects($this->exactly(2))
            ->method('findBy')
            ->willReturnCallback($findByReturnCallback);

        $this->tester->setInputs(['fooprovider', 'foo', 'small']);

        $statusCode = $this->tester->execute(['command' => $this->command->getName()]);

        $this->assertStringEndsWith('Done (total medias processed: 2).'.\PHP_EOL, $this->tester->getDisplay());

        $this->assertSame(0, $statusCode);
    }
}
