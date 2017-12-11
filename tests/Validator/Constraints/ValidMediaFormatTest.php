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

namespace Sonata\MediaBundle\Tests\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Validator\Constraints\ValidMediaFormat;

class ValidMediaFormatTest extends TestCase
{
    public function testInstance(): void
    {
        $constraint = new ValidMediaFormat();

        $this->assertSame('class', $constraint->getTargets());
        $this->assertSame('sonata.media.validator.format', $constraint->validatedBy());
    }
}
