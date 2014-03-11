<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Validator;

use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FormatValidator extends ConstraintValidator
{
    protected $pool;

    /**
     * @param Pool $pool
     */
    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $formats = $this->pool->getFormatNamesByContext($value->getContext());

        if (!$value instanceof GalleryInterface) {
            $this->context->addViolationAtPath('defaultFormat', 'Invalid instance, expected GalleryInterface');
        }

        if (!array_key_exists($value->getDefaultFormat(), $formats)) {
            $this->context->addViolation('invalid format');
        }
    }
}
