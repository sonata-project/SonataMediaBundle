<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Form\DataTransformer;

use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProviderDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testReverseTransformFakeValue()
    {
        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();

        $transformer = new ProviderDataTransformer($pool, 'stdClass');
        $this->assertEquals('foo', $transformer->reverseTransform('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReverseTransformUnknowProvider()
    {
        $pool = new Pool('default');

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(3))->method('getProviderName')->will($this->returnValue('unknow'));
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue('xcs'));

        $transformer = new ProviderDataTransformer($pool, 'stdClass', array(
            'new_on_update' => false
        ));
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformValidProvider()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('transform');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(3))->method('getProviderName')->will($this->returnValue('default'));
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue('xcs'));

        $transformer = new ProviderDataTransformer($pool, 'stdClass', array(
            'new_on_update' => false
        ));
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithNewMediaAndNoBinaryContent()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(null));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue(null));
        $media->expects($this->any())->method('getProviderName')->will($this->returnValue('default'));

        $transformer = new ProviderDataTransformer($pool, 'stdClass', array(
            'new_on_update' => false,
            'empty_on_new' => false
        ));
        $this->assertEquals($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithMediaAndNoBinaryContent()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue(null));

        $transformer = new ProviderDataTransformer($pool, 'stdClass');
        $this->assertEquals($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithMediaAndUploadFileInstance()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(3))->method('getProviderName')->will($this->returnValue('default'));
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue(new UploadedFile(__FILE__, 'ProviderDataTransformerTest')));

        $transformer = new ProviderDataTransformer($pool, 'stdClass', array(
            'new_on_update' => false
        ));
        $transformer->reverseTransform($media);

    }
}
