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

/**
 * @author Anton Dyshkant <vyshkant@gmail.com>
 *
 * @requires function Symfony\Component\Console\Tester\CommandTester::setInputs
 */
final class RemoveThumbsCommandTest extends FilesystemTestCase
{
    private Application $application;

    private RemoveThumbsCommand $command;

    private CommandTester $tester;

    private Pool $pool;

    /**
     * @var MockObject&MediaManagerInterface
     */
    private MockObject $mediaManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->pool = new Pool('default');

        $this->command = new RemoveThumbsCommand($this->pool, $this->mediaManager);

        $this->application = new Application();
        $this->application->add($this->command);

        $this->tester = new CommandTester($this->application->find('sonata:media:remove-thumbnails'));
    }

    public function testExecuteWithoutArguments(): void
    {
        $this->filesystem->mkdir($this->workspace.\DIRECTORY_SEPARATOR.'foo');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_1_foo.ext');
        $this->filesystem->touch($this->workspace.\DIRECTORY_SEPARATOR.'foo'.\DIRECTORY_SEPARATOR.'thumb_2_bar.ext');

        $formats = [
            'small' => [],
        ];

        $fileProvider = $this->createMock(FileProvider::class);

        $fileProvider
            ->method('getName')
            ->willReturn('fooprovider');
        $fileProvider->expects(static::once())
            ->method('getFormats')
            ->willReturn($formats);
        $fileProvider->expects(static::exactly(2))
            ->method('removeThumbnails');
        $fileProvider->expects(static::exactly(2))
            ->method('getFilesystem')
            ->willReturn($this->createMock(Filesystem::class));

        $this->pool->addContext('foo');
        $this->pool->addProvider('fooprovider', $fileProvider);

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
            ?int $limit = null,
            ?int $offset = null
        ) use ($medias): array {
            if (null !== $offset && $offset > 0) {
                return [];
            }

            return $medias;
        };

        $this->mediaManager->expects(static::exactly(2))
            ->method('findBy')
            ->willReturnCallback($findByReturnCallback);

        $this->tester->setInputs(['fooprovider', 'foo', 'small']);

        $statusCode = $this->tester->execute(['command' => $this->command->getName()]);

        static::assertStringEndsWith('Done (total medias processed: 2).'.\PHP_EOL, $this->tester->getDisplay());

        static::assertSame(0, $statusCode);
    }
}
