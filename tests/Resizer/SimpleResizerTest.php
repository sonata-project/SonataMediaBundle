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

namespace Sonata\MediaBundle\Tests\Resizer;

use Gaufrette\Adapter\InMemory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\SimpleResizer;

/**
 * @phpstan-import-type FormatOptions from MediaProviderInterface
 */
class SimpleResizerTest extends TestCase
{
    public function testResizeWithIncorrectMode(): void
    {
        $image = $this->createStub(ImageInterface::class);
        $adapter = $this->createStub(ImagineInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $metadata = $this->createStub(MetadataBuilderInterface::class);
        $file = $this->createStub(File::class);

        $media->expects(static::once())->method('getBox')->willReturn(new Box(535, 132));

        $adapter->method('load')->willReturn($image);

        $resizer = new SimpleResizer($adapter, ManipulatorInterface::THUMBNAIL_FLAG_NOCLONE, $metadata);

        $this->expectException(InvalidArgumentException::class);

        $resizer->resize($media, $file, $file, 'bar', [
            'height' => null,
            'width' => 90,
            'quality' => 100,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
    }

    public function testResizeWithNoWidthAndHeight(): void
    {
        $this->expectException(\RuntimeException::class);

        $adapter = $this->createStub(ImagineInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $metadata = $this->createStub(MetadataBuilderInterface::class);
        $file = $this->createStub(File::class);

        $resizer = new SimpleResizer($adapter, ManipulatorInterface::THUMBNAIL_INSET, $metadata);
        $resizer->resize($media, $file, $file, 'bar', [
            'width' => null,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
    }

    public function testResize(): void
    {
        $image = $this->createMock(ImageInterface::class);
        $image->expects(static::once())->method('thumbnail')->willReturn($image);
        $image->expects(static::once())->method('get')->willReturn(file_get_contents(__DIR__.'/../Fixtures/logo.png'));

        $adapter = $this->createStub(ImagineInterface::class);
        $adapter->method('load')->willReturn($image);

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::exactly(2))->method('getBox')->willReturn(new Box(535, 132));

        $filesystem = new Filesystem(new InMemory());
        $in = $filesystem->get('in', true);

        $fileContents = file_get_contents(__DIR__.'/../Fixtures/logo.png');

        if (false === $fileContents) {
            static::fail('Unable to read "logo.png" file.');
        }

        $in->setContent($fileContents);

        $out = $filesystem->get('out', true);

        $metadata = $this->createMock(MetadataBuilderInterface::class);
        $metadata->expects(static::once())->method('get')->willReturn([]);

        $resizer = new SimpleResizer($adapter, ManipulatorInterface::THUMBNAIL_OUTBOUND, $metadata);
        $resizer->resize($media, $in, $out, 'bar', [
            'height' => null,
            'width' => 90,
            'quality' => 100,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
    }

    /**
     * @dataProvider getBoxSettings
     *
     * @param array<string, int|string|bool|array|null> $settings
     *
     * @phpstan-param FormatOptions $settings
     */
    public function testGetBox(int $mode, array $settings, Box $mediaSize, Box $result): void
    {
        $adapter = $this->createStub(ImagineInterface::class);

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::exactly(2))->method('getBox')->willReturn($mediaSize);

        $metadata = $this->createStub(MetadataBuilderInterface::class);

        $resizer = new SimpleResizer($adapter, $mode, $metadata);

        $box = $resizer->getBox($media, $settings);

        static::assertInstanceOf(Box::class, $box);

        static::assertSame($result->getWidth(), $box->getWidth());
        static::assertSame($result->getHeight(), $box->getHeight());
    }

    /**
     * @phpstan-return iterable<array{int, FormatOptions, Box, Box}>
     */
    public static function getBoxSettings(): iterable
    {
        yield [ManipulatorInterface::THUMBNAIL_INSET, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(100, 120), new Box(75, 90)];

        yield [ManipulatorInterface::THUMBNAIL_INSET, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_INSET, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_INSET, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(567, 200), new Box(90, 32)];

        yield [ManipulatorInterface::THUMBNAIL_INSET, [
            'width' => 100,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(567, 200), new Box(100, 35)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(100, 120), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(120, 100), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(567, 50), new Box(90, 8)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND | ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(100, 120), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND | ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(120, 100), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND | ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE, [
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND | ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(90, 90)];

        yield [ManipulatorInterface::THUMBNAIL_OUTBOUND | ManipulatorInterface::THUMBNAIL_FLAG_UPSCALE, [
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(567, 50), new Box(90, 8)];
    }
}
