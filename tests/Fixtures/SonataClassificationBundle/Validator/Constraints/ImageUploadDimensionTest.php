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

namespace Sonata\ClassificationBundle\Validator\Constraints;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimension;
use Sonata\MediaBundle\Validator\Constraints\ImageUploadDimensionValidator;

final class ImageUploadDimensionTest extends TestCase
{
    public function testInstance(): void
    {
        $constraint = new ImageUploadDimension();

        static::assertSame('class', $constraint->getTargets());
        static::assertSame(ImageUploadDimensionValidator::class, $constraint->validatedBy());
    }
}
