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
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\SquareResizer;

class SquareResizerTest extends TestCase
{
    public function testResizeWithNoWidth(): void
    {
        $this->expectException(\RuntimeException::class);

        $adapter = $this->createMock(ImagineInterface::class);
        $media = $this->createMock(MediaInterface::class);
        $file = $this->createMock(File::class);
        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $resizer = new SquareResizer($adapter, 'foo', $metadata);
        $resizer->resize($media, $file, $file, 'bar', []);
    }

    /**
     * @dataProvider getBoxSettings
     */
    public function testGetBox($settings, Box $mediaSize, Box $expected): void
    {
        $adapter = $this->createMock(ImagineInterface::class);

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->once())->method('getBox')->will($this->returnValue($mediaSize));

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $resizer = new SquareResizer($adapter, 'foo', $metadata);

        $box = $resizer->getBox($media, $settings);

        $this->assertInstanceOf(Box::class, $box);

        $this->assertSame($expected->getWidth(), $box->getWidth());
        $this->assertSame($expected->getHeight(), $box->getHeight());
    }

    public static function getBoxSettings()
    {
        return [
            [['width' => 90, 'height' => 90], new Box(100, 120), new Box(90, 90)],
            [['width' => 90, 'height' => 90], new Box(50, 50), new Box(50, 50)],
            [['width' => 90, 'height' => null], new Box(50, 50), new Box(50, 50)],
            [['width' => 90, 'height' => null], new Box(567, 50), new Box(90, 7)],
        ];
    }
}
