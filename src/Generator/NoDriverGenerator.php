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

namespace Sonata\MediaBundle\Generator;

use Sonata\MediaBundle\Exception\NoDriverException;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @internal
 *
 * @author Andrey F. Mindubaev <covex.mobile@gmail.com>
 */
final class NoDriverGenerator implements GeneratorInterface
{
    public function generatePath(MediaInterface $media): void
    {
        throw new NoDriverException();
    }
}
