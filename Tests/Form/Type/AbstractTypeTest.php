<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Form\Type;

use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 */
abstract class AbstractTypeTest extends TypeTestCase
{
    /**
     * @var FormBuilder
     */
    protected $formBuilder;

    /**
     * @var FormTypeInterface
     */
    protected $formType;

    /**
     * @var Pool
     */
    protected $mediaPool;

    protected function setUp()
    {
        $provider = $this->getMockBuilder('Sonata\MediaBundle\Provider\MediaProviderInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $this->mediaPool->expects($this->any())->method('getProvider')->willReturn($provider);

        // NEXT_MAJOR: Hack for php 5.3 only, remove it when requirement of PHP is >= 5.4
        $that = $this;

        $this->formBuilder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')->disableOriginalConstructor()->getMock();
        $this->formBuilder
            ->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function ($name, $type = null) use ($that) {
                if (null !== $type) {
                    $that->assertTrue(class_exists($type), sprintf('Unable to ensure %s is a FQCN', $type));
                }
            }));

        $this->formType = $this->getTestedInstance();
    }

    public function testBuildForm()
    {
        $this->formType->buildForm($this->formBuilder, array(
            'provider_name' => 'sonata.media.provider.image',
            'provider' => null,
            'context' => null,
            'empty_on_new' => true,
            'new_on_update' => true,
        ));
    }

    public function testGetParent()
    {
        $parentRef = $this->formType->getParent();

        $this->assertTrue(class_exists($parentRef), sprintf('Unable to ensure %s is a FQCN', $parentRef));
    }

    /**
     * Get the tested form type instance.
     *
     * @return FormTypeInterface
     */
    abstract protected function getTestedInstance();
}
