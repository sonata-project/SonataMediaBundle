<?php

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
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Resizer\SquareResizer;

class SquareResizerTest extends TestCase
{
    public function testResizeWithNoWidth()
    {
        $this->expectException(\RuntimeException::class);

        $adapter = $this->createMock('Imagine\Image\ImagineInterface');
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $file = $this->getMockBuilder('Gaufrette\File')->disableOriginalConstructor()->getMock();
        $metadata = $this->createMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $resizer = new SquareResizer($adapter, 'foo', $metadata);
        $resizer->resize($media, $file, $file, 'bar', []);
    }

    /**
     * @dataProvider getBoxSettings
     */
    public function testGetBox($settings, Box $mediaSize, Box $expected)
    {
        $adapter = $this->createMock('Imagine\Image\ImagineInterface');

        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->once())->method('getBox')->will($this->returnValue($mediaSize));

        $metadata = $this->createMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $resizer = new SquareResizer($adapter, 'foo', $metadata);

        $box = $resizer->getBox($media, $settings);

        $this->assertInstanceOf('Imagine\Image\Box', $box);

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
