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
     * @param MediaInterface $media
     * @param File           $in
     * @param File           $out
     * @param string         $format
     * @param array          $settings
     *
     * @return void
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings);

    /**
     * @param MediaInterface $media
     * @param array          $settings
     *
     * @return \Imagine\Image\Box
     */
    public function getBox(MediaInterface $media, array $settings);
}
