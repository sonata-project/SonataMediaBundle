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

final class FormatValidator extends ConstraintValidator
{
    public function __construct(private Pool $pool)
    {
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        $formats = $this->pool->getFormatNamesByContext($value->getContext());

        if (!$value instanceof GalleryInterface) {
            $this->context->buildViolation('Invalid instance, expected GalleryInterface')
               ->atPath('defaultFormat')
               ->addViolation();
        }

        $galleryDefaultFormat = $value->getDefaultFormat();

        if (MediaProviderInterface::FORMAT_REFERENCE !== $galleryDefaultFormat
            && !\array_key_exists($galleryDefaultFormat, $formats)) {
            $this->context->addViolation('invalid format');
        }
    }
}
