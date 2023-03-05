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

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class ValidMediaFormat extends Constraint
{
    public string $message = 'The format is not valid';

    public function validatedBy(): string
    {
        return 'sonata.media.validator.format';
    }

    /**
     * @return string|string[]
     */
    #[\ReturnTypeWillChange]
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
