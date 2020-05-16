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

final class ImageUploadDimension extends Constraint
{
    public $message = 'error.image_too_small';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
