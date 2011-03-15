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

use Imagine\Image\BoxInterface;
use Imagine\Image\Box;
    
class SimpleResizer implements ResizerInterface
{
    protected $adapterClass;

    protected $mode;
    
    public function __construct($adapter, $mode)
    {
        $this->adapter = $adapter;
        $this->mode    = $mode;
    }

    public function resize($in, $out, $width, $height = null)
    {

        $image = $this->getAdapter()->open($in);

        if($height == null) {
            $size = $image->getSize();
            $height = (int) ($width * $size->getHeight() / $size->getWidth());
        }

        return $image
            ->thumbnail(new Box($width, $height), $this->getMode())
            ->save($out);
    }

    /**
     * 
     * @return \Imagine\Image\BoxInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    public function getMode()
    {
        return $this->mode;
    }
}