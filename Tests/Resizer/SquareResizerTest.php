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
use Sonata\MediaBundle\Resizer\SquareResizer;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class SquareResizerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testResizeWithNoWidth()
    {
        $adapter = $this->createMock('Imagine\Image\ImagineInterface');
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $file = $this->getMockBuilder('Gaufrette\File')->disableOriginalConstructor()->getMock();
        $metadata = $this->createMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $resizer = new SquareResizer($adapter, 'foo', $metadata);
        $resizer->resize($media, $file, $file, 'bar', array());
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
        return array(
            array(array('width' => 90, 'height' => 90), new Box(100, 120), new Box(100, 100)),
            array(array('width' => 90, 'height' => 90), new Box(50, 50), new Box(50, 50)),
            array(array('width' => 90, 'height' => null), new Box(50, 50), new Box(50, 50)),
            array(array('width' => 90, 'height' => null), new Box(567, 50), new Box(90, 7)),
        );
    }
}
