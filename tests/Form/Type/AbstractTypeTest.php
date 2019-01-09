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

namespace Sonata\MediaBundle\Tests\Form\Type;

use Sonata\MediaBundle\Provider\MediaProviderInterface;
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

    protected function setUp(): void
    {
        $provider = $this->getMockBuilder(MediaProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mediaPool = $this->createMock(Pool::class);
        $this->mediaPool->expects($this->any())->method('getProvider')->willReturn($provider);

        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->formBuilder
            ->expects($this->any())
            ->method('add')
            ->will($this->returnCallback(function ($name, $type = null): void {
                if (null !== $type) {
                    $this->assertTrue(class_exists($type), sprintf('Unable to ensure %s is a FQCN', $type));
                }
            }));

        $this->formType = $this->getTestedInstance();
    }

    public function testBuildForm(): void
    {
        $this->formType->buildForm($this->formBuilder, [
            'provider_name' => 'sonata.media.provider.image',
            'provider' => null,
            'context' => null,
            'empty_on_new' => true,
            'new_on_update' => true,
        ]);
    }

    public function testGetParent(): void
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
