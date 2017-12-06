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

use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\Form\Forms;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 */
class MediaTypeTest extends AbstractTypeTest
{
    protected $mediaPool;

    /**
     * @var MediaType
     */
    protected $mediaType;

    protected function setUp()
    {
        parent::setUp();

        $this->mediaPool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $this->mediaType = new MediaType($this->mediaPool, 'testClass');

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType($this->mediaType)
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    public function testMissingFormOptions()
    {
        $this->mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]));
        $this->mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue([
            'video' => [],
            'pic' => [],
        ]));

        $this->expectException(
            'Symfony\Component\OptionsResolver\Exception\MissingOptionsException'
        );
        $this->expectExceptionMessage(
            'The required options "context", "provider" are missing.'
        );

        $this->factory->create($this->getFormType(), null);
    }

    public function testMissingFormContextOption()
    {
        $this->mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]));
        $this->mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue([
            'video' => [],
            'pic' => [],
        ]));

        $this->expectException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_a',
        ]);
    }

    public function testMissingFormProviderOption()
    {
        $this->mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]));
        $this->mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue([
            'video' => [],
            'pic' => [],
        ]));

        $this->expectException('Symfony\Component\OptionsResolver\Exception\MissingOptionsException');

        $this->factory->create($this->getFormType(), null, [
            'context' => 'pic',
        ]);
    }

    public function testInvalidFormProviderOption()
    {
        $this->mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]));
        $this->mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue([
            'video' => [],
            'pic' => [],
        ]));

        // NEXT_MAJOR: Remove this hack when dropping support for symfony 2.3
        if (class_exists('Symfony\Component\Validator\Validator\RecursiveValidator')) {
            $this->expectException(
                'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
            );
            $this->expectExceptionMessage(
                'The option "provider" with value "provider_c" is invalid. Accepted values are: "provider_a", "provider_b".'
            );
        } else {
            $this->expectException(
                'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
            );
            $this->expectExceptionMessage(
                'The option "provider" has the value "provider_c", but is expected to be one of "provider_a", "provider_b"'
            );
        }

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_c',
            'context' => 'pic',
        ]);
    }

    public function testInvalidFormContextOption()
    {
        $this->mediaPool->expects($this->any())->method('getProviderList')->will($this->returnValue([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]));
        $this->mediaPool->expects($this->any())->method('getContexts')->will($this->returnValue([
            'video' => [],
            'pic' => [],
        ]));

        // NEXT_MAJOR: Remove this hack when dropping support for symfony 2.3
        if (class_exists('Symfony\Component\Validator\Validator\RecursiveValidator')) {
            $this->expectException(
                'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
            );
            $this->expectExceptionMessage(
                'The option "context" with value "photo" is invalid. Accepted values are: "video", "pic".'
            );
        } else {
            $this->expectException(
                'Symfony\Component\OptionsResolver\Exception\InvalidOptionsException'
            );
            $this->expectExceptionMessage(
                'The option "context" has the value "photo", but is expected to be one of "video", "pic"'
            );
        }

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_b',
            'context' => 'photo',
        ]);
    }

    protected function getTestedInstance()
    {
        return new MediaType($this->mediaPool, 'testclass');
    }

    private function getFormType()
    {
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') ?
            'Sonata\MediaBundle\Form\Type\MediaType' :
            'sonata_media_type';
    }
}
