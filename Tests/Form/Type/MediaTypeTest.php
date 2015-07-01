<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Form\Type;

use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class MediaTypeTest extends TypeTestCase
{
    /**
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage The required options "context", "provider" are missing.
     */
    public function testMissingFormOptions()
    {
        $mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue(array(
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        )));
        $mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue(array(
            'video' => array(),
            'pic' => array(),
        )));
        $type = new MediaType($mediaPool, 'testClass');
        $this->factory->create($type);
    }

    /**
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage The required option "context" is missing.
     */
    public function testMissingFormContextOption()
    {
        $mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue(array(
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        )));
        $mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue(array(
            'video' => array(),
            'pic' => array(),
        )));
        $type = new MediaType($mediaPool, 'testClass');
        $this->factory->create($type, null, array('provider' => 'sonata.media.provider.image'));
    }

    /**
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @expectedExceptionMessage The required option "provider" is missing.
     */
    public function testMissingFormProviderOption()
    {
        $mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue(array(
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        )));
        $mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue(array(
            'video' => array(),
            'pic' => array(),
        )));
        $type = new MediaType($mediaPool, 'testClass');
        $this->factory->create($type, null, array('context' => 'photo'));
    }

    /**
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The option "provider" with value "provider_c" is invalid. Accepted values are: "provider_a", "provider_b".
     */
    public function testInvalidFormProviderOption()
    {
        $mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue(array(
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        )));
        $mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue(array(
            'video' => array(),
            'pic' => array(),
        )));
        $type = new MediaType($mediaPool, 'testClass');
        $this->factory->create($type, null, array('provider' => 'provider_c', 'context' => 'photo'));
    }

    /**
     * @expectedException        \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @expectedExceptionMessage The option "context" with value "photo" is invalid. Accepted values are: "video", "pic".
     */
    public function testInvalidFormContextOption()
    {
        $mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue(array(
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        )));
        $mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue(array(
            'video' => array(),
            'pic' => array(),
        )));
        $type = new MediaType($mediaPool, 'testClass');
        $this->factory->create($type, null, array('provider' => 'provider_b', 'context' => 'photo'));
    }
}
