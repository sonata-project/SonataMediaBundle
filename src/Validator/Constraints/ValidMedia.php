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

namespace Sonata\MediaBundle\Validator\Constraints;

use Sonata\MediaBundle\Validator\MediaValidator;
use Symfony\Component\Validator\Constraint;

class ValidMedia extends Constraint
{
    public function validatedBy(): string
    {
        return MediaValidator::class;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
