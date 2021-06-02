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

use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class PathGenerator implements GeneratorInterface
{
    public function generatePath(MediaInterface $media)
    {
        $segments = preg_split('#/#', $media->getId(), -1, PREG_SPLIT_NO_EMPTY);

        if (\count($segments) > 1) {
            // remove last part from id
            array_pop($segments);
            $path = implode('/', $segments);
        } else {
            $path = '';
        }

        return $path ? sprintf('%s/%s', $media->getContext(), $path) : $media->getContext();
    }
}
