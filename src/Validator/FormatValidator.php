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

use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class FormatValidator extends ConstraintValidator
{
    /**
     * @var Pool
     */
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $formats = $this->pool->getFormatNamesByContext($value->getContext());

        if (!$value instanceof GalleryInterface) {
            $this->context->buildViolation('Invalid instance, expected GalleryInterface')
               ->atPath('defaultFormat')
               ->addViolation();
        }

        $galleryDefaultFormat = $value->getDefaultFormat();

        if (MediaProviderInterface::FORMAT_REFERENCE !== $galleryDefaultFormat
            && !($formats && \array_key_exists($galleryDefaultFormat, $formats))) {
            $this->context->addViolation('invalid format');
        }
    }
}
