<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Resizer;

use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;

interface ResizerInterface
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * @return void
     */
    function resize(MediaInterface $media, File $in, File $out, $format, array $settings);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param array $settings
     * @return \Imagine\Image\Box
     */
    function getBox(MediaInterface $media, array $settings);
}