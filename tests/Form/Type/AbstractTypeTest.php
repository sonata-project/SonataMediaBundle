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
        $provider = $this->createMock(MediaProviderInterface::class);

        $this->mediaPool = $this->createMock(Pool::class);
        $this->mediaPool->method('getProvider')->willReturn($provider);

        $this->formType = $this->getTestedInstance();
    }

    abstract protected function getTestedInstance(): FormTypeInterface;
}
