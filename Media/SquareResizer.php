<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Sonata Project
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Media;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * This reziser crop the image when the width and height are specified.
 * Every time you specify the W and H, the script generate a square with the
 * smaller size. For example, if width is 100 and height 80; the generated image
 * will be 80x80.
 *
 * @author Edwin Ibarra <edwines@feniaz.com>
 */
class SquareResizer implements ResizerInterface
{
    /**
     * ImagineInterface
     */
    protected $adapter;
    
    /**
     * string
     */
    protected $mode;

    /**
     * @param \Imagine\Image\ImagineInterface $adapter
     * @param string $mode
     */
    public function __construct(ImagineInterface $adapter, $mode)
    {
        $this->adapter = $adapter;
        $this->mode    = $mode;
    }
    
    /**
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * 
     * @return void
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, $settings)
    {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        $image = $this->getAdapter()->load($in->getContent());
        $size = $image->getSize();

        if (null != $settings['height']) {
            if ($size->getHeight() > $size->getWidth()) {
                $higher = $size->getHeight();
                $lower = $size->getWidth();
            } else {
                $higher = $size->getWidth();
                $lower = $size->getHeight();
            }
            
            $crop = $higher - $lower;
            
            if ($crop > 0) {
                $point = $higher == $size->getHeight() 
                    ? new Point(0, 0)
                    : new Point($crop/2, 0);
                
                $image->crop($point, new Box($lower, $lower));
            }
        } else {
            $settings['height'] = (int) ($settings['width'] * $size->getHeight() / $size->getWidth());
        }

        if ($settings['height'] < $size->getHeight() && $settings['width'] < $size->getWidth()) {
            $content = $image
                ->thumbnail(new Box($settings['width'], $settings['height']), $this->getMode())
                ->get($format);
        } else {
            $content = $image->get($format);
        }

        $out->setContent($content);
    }
    
    /**
     * @return \Imagine\Image\ImagineInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}