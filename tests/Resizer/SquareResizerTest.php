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

use Gaufrette\File;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\SquareResizer;

/**
 * @phpstan-import-type FormatOptions from MediaProviderInterface
 */
class SquareResizerTest extends TestCase
{
    public function testResizeWithNoWidth(): void
    {
        $this->expectException(\RuntimeException::class);

        $adapter = $this->createMock(ImagineInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $file = $this->createMock(File::class);
        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $resizer = new SquareResizer($adapter, ManipulatorInterface::THUMBNAIL_INSET, $metadata);
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

    /**
     * @dataProvider getBoxSettings
     *
     * @param array<string, int|string|bool|array|null>|false $settings
     *
     * @phpstan-param FormatOptions $settings
     */
    public function testGetBox(array $settings, Box $mediaSize, Box $expected): void
    {
        $adapter = $this->createMock(ImagineInterface::class);

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::once())->method('getBox')->willReturn($mediaSize);

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $resizer = new SquareResizer($adapter, ManipulatorInterface::THUMBNAIL_INSET, $metadata);

        $box = $resizer->getBox($media, $settings);

        static::assertInstanceOf(Box::class, $box);

        static::assertSame($expected->getWidth(), $box->getWidth());
        static::assertSame($expected->getHeight(), $box->getHeight());
    }

    /**
     * @phpstan-return iterable<array{FormatOptions, Box, Box}>
     */
    public static function getBoxSettings(): iterable
    {
        yield [[
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(100, 120), new Box(90, 90)];

        yield [[
            'width' => 90,
            'height' => 90,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(50, 50)];

        yield [[
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(50, 50), new Box(50, 50)];

        yield [[
            'width' => 90,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ], new Box(567, 50), new Box(90, 7)];
    }
}
