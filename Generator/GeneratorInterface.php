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

interface GeneratorInterface
{

    /**
     * @abstract
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaInterface $media);
}
