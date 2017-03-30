<?php

namespace Application\Sonata\MediaBundle\Resizer;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Gaufrette\File;

/**
 * CropResizer
 * 
 * CropResizer provides helper methods to resize and crop images 
 * without deforming the original ratio. It works in absolutely any case.
 * 
 * @author Pavel Petrov <p.petrov@akristo.com>
 * @author Zlatko Hristov <zlatko.2create@gmail.com>
 * @author Todor Todorov <todstychev@gmail.com>
 * 
 * @version 0.1 beta
 */
class CropResizer implements ResizerInterface {

    protected $adapter;
    protected $mode;
    protected $metadata;

    /**
     * @param ImagineInterface $adapter
     * @param string           $mode
     */
    public function __construct(ImagineInterface $adapter, $mode, MetadataBuilderInterface $metadata) {
        $this->adapter = $adapter;
        $this->mode = $mode;
        $this->metadata = $metadata;
    }

    /**
     * resize
     * 
     * Resizes and crops the image
     * 
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $in
     * @param \Gaufrette\File $out
     * @param string $format
     * @param array $settings
     * @throws \RuntimeException
     */
    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings) {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }
        if (!isset($settings['height'])) {
            throw new \RuntimeException(sprintf('Height parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }

        // Gets the image
        $image = $this->adapter->load($in->getContent());

        // Checks the settings
        if (!isset($settings['height']) || $settings['height'] === false) {
            $content = $image
                    ->thumbnail($this->getBox($media, $settings), $this->mode)
                    ->get($format, array('quality' => $settings['quality']));
        } else {
            
            // Get the new size
            $size = $this->getSize($media, $settings);

            // Resize and crop the image
            $content = $image
                    ->resize($this->getBox($media, $size))
                    ->crop($this->findCropPoint($size['height'], $size['width'], $settings['height'], $settings['width']), new Box((int) $settings['width'], (int) $settings["height"]))
                    ->get($format, array('quality' => $settings['quality']));
        }

        // Output the result
        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    /**
     * getBox
     * 
     * Returns an Box instance
     * 
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param array $settings
     * @return \Imagine\Image\Box
     */
    public function getBox(MediaInterface $media, array $settings) {
        if ($settings['height'] == null) {
            $settings['height'] = (int) ($settings['width'] * $media->getBox()->getHeight() / $media->getBox()->getWidth());
        }

        return new Box($settings["width"], $settings["height"]);
    }
    
    /**
     * getSize
     * 
     * Calculates the new size for the image and returns it 
     * 
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param array $settings
     * @return array
     */
    public function getSize(MediaInterface $media, $settings) {
        $heightRatio = $media->getBox()->getHeight() / $settings['height'];
        $widthRatio = $media->getBox()->getWidth() / $settings['width'];

        $ratio = min($heightRatio, $widthRatio);

        $width = $media->getBox()->getWidth() / $ratio;
        $height = $media->getBox()->getHeight() / $ratio;
        
        // Round the values, because you can have an odd number and this gives an
        // error in the further usage of those values.
        $h = round($height, 0);
        $w = round($width, 0);
        
        return array('width' => $w, 'height' => $h);
    }
    
    /**
     * findCropPoint
     * 
     * This method takes the differences in the width and height of the input and
     * output image, then devides those differences on 2 and finds the half of the 
     * differences. Then the method sets the a new Point object with those values
     * and returns it. 
     * 
     * @example 
     * Input image widht x height 1000 2000, output image width and height 500 x 
     * 1000. 
     * The difference in the height is 500 on the width 1000.
     * Divided by 2 @var $y becomes 250, @var $x becomes 500
     * Those are the initial points for the cropping.
     * 
     * @param integer $inputH
     * @param integer $inputW
     * @param integer $outputH
     * @param integer $outputW
     * @return \Imagine\Image\Point
     */
    public function findCropPoint($inputH, $inputW, $outputH, $outputW) {
        
        if ($inputH == $outputH) {
            $x = ($inputW - $outputW) / 2;
            $y = 0;
        } else if ($inputW == $outputW) {
            $x = 0;
            $y = ($inputH - $outputH) / 2;
        } else if ($inputH > $outputH && $inputW > $outputW) {
            $x = ($inputH - $outputH) / 2;
            $y = ($inputW - $outputW) / 2;
        } else {
            $x = 0;
            $y = 0;
        }
        
        return new Point($x, $y);
    }

   
}
