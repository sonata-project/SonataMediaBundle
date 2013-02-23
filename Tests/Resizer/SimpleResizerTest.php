<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Resizer;

use Sonata\MediaBundle\Resizer\SimpleResizer;
use Imagine\Image\Box;
use Gaufrette\File;
use Gaufrette\Adapter\InMemory;
use Gaufrette\Filesystem;

class SimpleResizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testResizeWithNoWidth()
    {
        $adapter = $this->getMock('Imagine\Image\ImagineInterface');
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');
        $file = $this->getMockBuilder('Gaufrette\File')->disableOriginalConstructor()->getMock();

        $resizer = new SimpleResizer($adapter, 'foo', $metadata);
        $resizer->resize($media, $file, $file, 'bar', array());
    }

    public function testResize()
    {

        $image = $this->getMock('Imagine\Image\ImageInterface');
        $image->expects($this->once())->method('thumbnail')->will($this->returnValue($image));
        $image->expects($this->once())->method('get')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/logo.png')));

        $adapter = $this->getMock('Imagine\Image\ImagineInterface');
        $adapter->expects($this->any())->method('load')->will($this->returnValue($image));

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(2))->method('getBox')->will($this->returnValue(new Box(535, 132)));

        $filesystem = new Filesystem(new InMemory);
        $in = $filesystem->get('in', true);
        $in->setContent(file_get_contents(__DIR__.'/../fixtures/logo.png'));

        $out = $filesystem->get('out', true);

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');
        $metadata->expects($this->once())->method('get')->will($this->returnValue(array()));

        $resizer = new SimpleResizer($adapter, 'outbound', $metadata);
        $resizer->resize($media, $in, $out, 'bar', array('height' => null, 'width' => 90, 'quality' => 100));
    }

    /**
     * @dataProvider getBoxSettings
     */
    public function testGetBox($mode, $settings, Box $mediaSize, Box $result)
    {
        $adapter = $this->getMock('Imagine\Image\ImagineInterface');

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(2))->method('getBox')->will($this->returnValue($mediaSize));

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $resizer = new SimpleResizer($adapter, $mode, $metadata);

        $box = $resizer->getBox($media, $settings);

        $this->assertInstanceOf('Imagine\Image\Box', $box);

        $this->assertEquals($result->getWidth(), $box->getWidth());
        $this->assertEquals($result->getHeight(), $box->getHeight());
    }

    public static function getBoxSettings()
    {
        return array(
            array('inset', array( 'width' => 90, 'height' => 90 ), new Box(100, 120), new Box(75, 90)),
            array('inset', array( 'width' => 90, 'height' => 90 ), new Box(50, 50), new Box(90, 90)),
            array('inset', array( 'width' => 90, 'height' => null ), new Box(50, 50), new Box(90, 90)),
            array('inset', array( 'width' => 90, 'height' => null ), new Box(567, 200), new Box(88, 31)),
            array('inset', array( 'width' => 100, 'height' => 100 ), new Box(567, 200), new Box(100, 35)),

            array('outbound', array( 'width' => 90, 'height' => 90 ), new Box(100, 120), new Box(90, 108)),
            array('outbound', array( 'width' => 90, 'height' => 90 ), new Box(50, 50), new Box(90, 90)),
            array('outbound', array( 'width' => 90, 'height' => null ), new Box(50, 50), new Box(90, 90)),
            array('outbound', array( 'width' => 90, 'height' => null ), new Box(567, 50), new Box(90, 8)),
        );
    }
}
