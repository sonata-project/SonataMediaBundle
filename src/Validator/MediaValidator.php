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

namespace Sonata\MediaBundle\Validator;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class MediaValidator extends ConstraintValidator
{
    public function __construct(private Pool $pool)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$value instanceof MediaInterface) {
            throw new UnexpectedValueException($value, MediaInterface::class);
        }

        $this->pool->validate($this->context, $value);
    }
}
