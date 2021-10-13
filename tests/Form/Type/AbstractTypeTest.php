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

use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 */
abstract class AbstractTypeTest extends TypeTestCase
{
    protected FormBuilder $formBuilder;

    protected FormTypeInterface $formType;

    protected Pool $mediaPool;

    protected function setUp(): void
    {
        $this->mediaPool = new Pool('default');
        $this->formType = $this->getTestedInstance();
    }

    abstract protected function getTestedInstance(): FormTypeInterface;
}
