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
/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ValidMediaFormat extends Constraint
{
    public $message = 'The format is not valid';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'sonata.media.validator.format';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
