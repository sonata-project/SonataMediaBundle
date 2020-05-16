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
use Sonata\MediaBundle\Provider\ImageProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class ImageUploadDimensionValidator extends ConstraintValidator
{
    /**
     * @var ImagineInterface
     */
    private $imagineAdapter;

    /**
     * @var ImageProvider
     */
    private $imageProvider;

    public function __construct(ImagineInterface $imagineAdapter, ImageProvider $imageProvider)
    {
        $this->imagineAdapter = $imagineAdapter;
        $this->imageProvider = $imageProvider;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof ImageUploadDimension) {
            throw new UnexpectedTypeException($constraint, ImageUploadDimension::class);
        }

        if (!$value instanceof MediaInterface) {
            throw new UnexpectedTypeException($value, MediaInterface::class);
        }

        if (null === $value->getBinaryContent()) {
            return;
        }

        $minWidth = 0;
        $minHeight = 0;

        foreach ($this->imageProvider->getFormatsForContext($value->getContext()) as $format) {
            if (!$format['constraint']) {
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
        } catch (\RuntimeException $e) {
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
