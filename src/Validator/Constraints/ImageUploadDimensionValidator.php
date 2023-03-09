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

use Imagine\Image\ImagineInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\ImageProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ImageUploadDimensionValidator extends ConstraintValidator
{
    public function __construct(
        private ImagineInterface $imagineAdapter,
        private ImageProviderInterface $imageProvider
    ) {
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ImageUploadDimension) {
            throw new UnexpectedTypeException($constraint, ImageUploadDimension::class);
        }

        if (!$value instanceof MediaInterface) {
            throw new UnexpectedTypeException($value, MediaInterface::class);
        }

        if (!$value->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        $context = $value->getContext();

        if (null === $context) {
            return;
        }

        $minWidth = 0;
        $minHeight = 0;

        foreach ($this->imageProvider->getFormatsForContext($context) as $format) {
            if (false === $format['constraint']) {
                continue;
            }

            $minWidth = max($minWidth, $format['width'] ?? 0);
            $minHeight = max($minHeight, $format['height'] ?? 0);
        }

        if (0 === $minWidth && 0 === $minHeight) {
            return;
        }

        try {
            $image = $this->imagineAdapter->open($value->getBinaryContent()->getPathname());
        } catch (\RuntimeException) {
            // Do nothing. The parent validator will throw a violation error.
            return;
        }

        $size = $image->getSize();

        if ($size->getWidth() < $minWidth || $size->getHeight() < $minHeight) {
            $this->context
                ->buildViolation($constraint->message, [
                    '%min_width%' => $minWidth,
                    '%min_height%' => $minHeight,
                ])
                ->setTranslationDomain('SonataMediaBundle')
                ->atPath('binaryContent')
                ->addViolation();
        }
    }
}
