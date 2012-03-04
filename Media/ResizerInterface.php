<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Media;

use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;
use Imagine\Image\Box;

interface ResizerInterface
{
    /**
     * @abstract
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * @return void
     */
    function resize(MediaInterface $media, File $in, File $out, $format, $settings);

    /**
     * @abstract
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param array $settings
     * @return Box
     */
    function getBox(MediaInterface $media, $settings);
}