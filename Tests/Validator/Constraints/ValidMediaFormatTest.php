<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Validator\Constraints;

use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;

class ValidMediaFormatTest extends \PHPUnit_Framework_TestCase
{

    public function testInstance()
    {
        $constraint = new ValidMediaFormat();

        $this->assertEquals('class', $constraint->getTargets());
        $this->assertEquals('sonata.media.validator.format', $constraint->validatedBy());
    }
}
