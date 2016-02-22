<?php

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

class PHPCRGenerator implements GeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generatePath(MediaInterface $media)
    {
        $segments = preg_split('#/#', $media->getId(), null, PREG_SPLIT_NO_EMPTY);

        if (count($segments) > 1) {
            // remove last part from id
            array_pop($segments);
            $path = implode($segments, '/');
        } else {
            $path = '';
        }

        return $path ? sprintf('%s/%s', $media->getContext(), $path) : $media->getContext();
    }
}
