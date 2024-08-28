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

class UuidGenerator implements GeneratorInterface
{
    public function generatePath(MediaInterface $media): string
    {
        $id = (string) $media->getId();
        $context = $media->getContext();

        if (null === $context) {
            throw new \InvalidArgumentException(\sprintf(
                'Unable to generate path for media without context using %s.',
                self::class
            ));
        }

        return \sprintf('%s/%04s/%02s', $context, substr($id, 0, 4), substr($id, 4, 2));
    }
}
