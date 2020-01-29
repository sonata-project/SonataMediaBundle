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
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\SimpleResizer;

class SimpleResizerTest extends TestCase
{
    public function testResizeWithNoWidthAndHeight(): void
    {
        $this->expectException(\RuntimeException::class);

        $adapter = $this->createMock(ImagineInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $metadata = $this->createMock(MetadataBuilderInterface::class);
        $file = $this->createMock(File::class);

        $resizer = new SimpleResizer($adapter, 'foo', $metadata);
        $resizer->resize($media, $file, $file, 'bar', []);
    }

    public function testResize(): void
    {
        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->once())->method('thumbnail')->willReturn($image);
        $image->expects($this->once())->method('get')->willReturn(file_get_contents(__DIR__.'/../fixtures/logo.png'));

        $adapter = $this->createMock(ImagineInterface::class);
        $adapter->method('load')->willReturn($image);

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->exactly(2))->method('getBox')->willReturn(new Box(535, 132));

        $filesystem = new Filesystem(new InMemory());
        $in = $filesystem->get('in', true);
        $in->setContent(file_get_contents(__DIR__.'/../fixtures/logo.png'));

        $out = $filesystem->get('out', true);

        $metadata = $this->createMock(MetadataBuilderInterface::class);
        $metadata->expects($this->once())->method('get')->willReturn([]);

        $resizer = new SimpleResizer($adapter, 'outbound', $metadata);
        $resizer->resize($media, $in, $out, 'bar', ['height' => null, 'width' => 90, 'quality' => 100]);
    }

    /**
     * @dataProvider getBoxSettings
     */
    public function testGetBox(string $mode, array $settings, Box $mediaSize, Box $result): void
    {
        $adapter = $this->createMock(ImagineInterface::class);

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->exactly(2))->method('getBox')->willReturn($mediaSize);

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $resizer = new SimpleResizer($adapter, $mode, $metadata);

        $box = $resizer->getBox($media, $settings);

        $this->assertInstanceOf(Box::class, $box);

        $this->assertSame($result->getWidth(), $box->getWidth());
        $this->assertSame($result->getHeight(), $box->getHeight());
    }

    public static function getBoxSettings(): array
    {
        return [
            ['inset', ['width' => 90, 'height' => 90], new Box(100, 120), new Box(75, 90)],
            ['inset', ['width' => 90, 'height' => 90], new Box(50, 50), new Box(90, 90)],
            ['inset', ['width' => 90, 'height' => null], new Box(50, 50), new Box(90, 90)],
            ['inset', ['width' => 90, 'height' => null], new Box(567, 200), new Box(90, 32)],
            ['inset', ['width' => 100, 'height' => 100], new Box(567, 200), new Box(100, 35)],

            ['outbound', ['width' => 90, 'height' => 90], new Box(100, 120), new Box(90, 90)],
            ['outbound', ['width' => 90, 'height' => 90], new Box(120, 100), new Box(90, 90)],
            ['outbound', ['width' => 90, 'height' => 90], new Box(50, 50), new Box(90, 90)],
            ['outbound', ['width' => 90, 'height' => null], new Box(50, 50), new Box(90, 90)],
            ['outbound', ['width' => 90, 'height' => null], new Box(567, 50), new Box(90, 8)],
        ];
    }
}
