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

use Gaufrette\Filesystem\File;
use Sonata\MediaBundle\Model\MediaInterface;

interface ResizerInterface
{
    /**
     * @abstract
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\Filesystem\File $in
     * @param \Gaufrette\Filesystem\File $out
     * @param string $format
     * @param array $settings
     * @return void
     */
    function resize(MediaInterface $media, File $in, File $out, $format, $settings);
}