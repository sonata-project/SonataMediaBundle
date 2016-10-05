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

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 */
class MediaTypeTest extends AbstractTypeTest
{
    protected function getTestedInstance()
    {
        return new MediaType($this->mediaPool, 'testclass');
    }
}
