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

use Imagine\ImagineInterface;
use Imagine\Image\Box;
use Gaufrette\Filesystem\File;
use Sonata\MediaBundle\Model\MediaInterface;

class SimpleResizer implements ResizerInterface
{
    protected $adapterClass;

    protected $mode;


    public function __construct(ImagineInterface $adapter, $mode)
    {
        $this->adapter = $adapter;
        $this->mode    = $mode;
    }

    /**
     * @param \Gaufrette\Filesystem\File $in
     * @param \Gaufrette\Filesystem\File $out
     * @param string $format
     * @param integer $width
     * @param null|integer $height
     * @return void
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, $width, $height = null)
    {
        $image = $this->getAdapter()->load($in->getContent());

        if ($height == null) {
            $size = $image->getSize();
            $height = (int) ($width * $size->getHeight() / $size->getWidth());
        }

        $content = $image
            ->thumbnail(new Box($width, $height), $this->getMode())
            ->get($format);

        $out->setContent($content);
    }

    /**
     * 
     * @return \Imagine\ImagineInterface
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