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

class ProviderDataTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testReverseTransformFakeValue()
    {
        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();

        $transformer = new ProviderDataTransformer($pool);
        $this->assertEquals('foo', $transformer->reverseTransform('foo'));
    }

    
    public function testReverseTransformUnknowProvider()
    {
        $pool = new Pool('default');

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(0))->method('getProviderName')->will($this->returnValue('unknow'));
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue('xcs'));

        $transformer = new ProviderDataTransformer($pool);
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformValidProvider()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('transform');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $uploadedFile = $this->getMock('\Symfony\Component\HttpFoundation\File\UploadedFile', array(), array('xcs', 'xcs', null, null, null, true), 'UploadedFile', false);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(2))->method('getProviderName')->will($this->returnValue('default'));
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));


        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue($uploadedFile));

        $transformer = new ProviderDataTransformer($pool);
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

        $transformer = new ProviderDataTransformer($pool);
        $this->assertNull($transformer->reverseTransform($media));
    }

    public function testReverseTransformWithMediaAndNoBinaryContent()
    {
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $media->expects($this->any())->method('getBinaryContent')->will($this->returnValue(null));

        $transformer = new ProviderDataTransformer($pool);
        $this->assertEquals($media, $transformer->reverseTransform($media));
    }
}
