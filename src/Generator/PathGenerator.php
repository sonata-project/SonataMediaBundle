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

final class PathGenerator implements GeneratorInterface
{
    public function generatePath(MediaInterface $media): string
    {
        $segments = preg_split('#/#', (string) $media->getId(), -1, \PREG_SPLIT_NO_EMPTY);

        if (false !== $segments && \count($segments) > 1) {
            // remove last part from id
            array_pop($segments);
            $path = implode('/', $segments);
        } else {
            $path = '';
        }

        $context = $media->getContext();

        if (null === $context) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to generate path for media without context using %s.',
                self::class
            ));
        }

        return '' !== $path ? \sprintf('%s/%s', $context, $path) : $context;
    }
}
