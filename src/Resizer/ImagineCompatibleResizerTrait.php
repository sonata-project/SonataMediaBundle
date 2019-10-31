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

namespace Sonata\MediaBundle\Resizer;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

/**
 * This trait is used to provide compatibility with Imagine >= 1.0.0.
 */
trait ImagineCompatibleResizerTrait
{
    /**
     * Convert mode for compatibility with imagine >= 1.0.0.
     *
     * @param int|string $mode
     *
     * @return int|string
     */
    final protected function convertMode($mode)
    {
        if (\is_string($mode) && version_compare(ImagineInterface::VERSION, '1.0.0', '>=')) {
            if ('inset' === $mode) {
                $mode = ImageInterface::THUMBNAIL_INSET;
            } elseif ('outbound' === $mode) {
                $mode = ImageInterface::THUMBNAIL_OUTBOUND;
            } elseif (is_numeric($mode)) {
                $mode = (int) $mode;
            }
        }

        return $mode;
    }
}
