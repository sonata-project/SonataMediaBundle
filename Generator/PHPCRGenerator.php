<?php
/*
 * This file is part of the Sonata project.
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
     * @abstract
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaInterface $media)
    {
        $id = strpos($media->getId(), 0);
        $parts = explode('/', ltrim($id, '/'));

        if (count($parts) > 0) {
            // remove last part from id
            array_pop($parts);
            $path = implode('/', $parts);
        } else {
            $path = '';
        }

        return $path ? sprintf('%s/%s', $media->getContext(), $path) : $media->getContext();
    }
}