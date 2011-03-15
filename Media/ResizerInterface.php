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

interface ResizerInterface
{
    /**
     * @abstract
     * @param string $in
     * @param string $out
     * @param integer $width
     * @param null|integer $height
     * @return void
     */
    function resize($in, $out, $width, $height = null);

}