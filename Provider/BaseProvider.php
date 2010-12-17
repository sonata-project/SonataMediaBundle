<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Provider;

use Bundle\MediaBundle\Entity\BaseMedia as Media;

abstract class BaseProvider
{

    protected $formats = array();
    protected $settings = array();

    public function __construct($name, $em, $settings = array())
    {

        $this->name     = $name;
        $this->em       = $em;
        $this->settings = $settings;
    }

    public function addFormat($name, $format)
    {

        $this->formats[$name] = $format;
    }

    public function getFormat($name)
    {
        
        return isset($this->formats[$name]) ? $this->formats[$name] : false;
    }

    public function requireThumbnails()
    {
        return true;
    }

    /**
     * generated thumbnails linked to the media, a thumbnail is a format used on the website
     *
     * @return void
     */
    public function generateThumbnails(Media $media)
    {

        if(!$this->requireThumbnails()) {
            return;
        }

        $base_path = $this->buildDirectory($media);
        $image_reference = $this->getReferenceImage($media);

        if (!is_array($this->formats) || count($this->formats) == 0) {

            throw new \RuntimeException('You must define one format');
        }

        if (substr($image_reference, 0, 7) == 'http://') {
            $info = pathinfo($image_reference);
            $temp_file = tempnam(sys_get_temp_dir(), 'image') . '.' . $info['extension'];
            file_put_contents($temp_file, file_get_contents($image_reference));
            $image_reference = $temp_file;
        }

        foreach ($this->formats as $format => $settings) {

            $filename = sprintf('%s/thumb_%s_%s.jpg',
                $base_path,
                $media->getId(),
                $format
            );

            if (is_file($filename)) {
                if (!@unlink($filename)) {
                    throw new \RuntimeException('Unable to unlink the file : ' . $filename . '. Please check permissions !');
                }
            }

            // TODO : this should be configured as a DI service
            $image = new \Imagine\Image($image_reference);

            // resize the thumbnail
            $processor = new \Imagine\Processor();
            $processor
                ->resize($settings['width'], $settings['constraint'] ? $settings['constraint'] :  $settings['height'])
                ->save($filename)
                ->process($image);
            
        }

        if (isset($temp_file)) {
            unlink($temp_file);
        }
    }

    /**
     * return the reference image of the media, can be the videa thumbnail or the original uploaded picture
     *
     * @abstract
     * @return string to the reference image
     */
    abstract function getReferenceImage(Media $media);

    /**
     * return the absolute path of the reference image or the service provider reference
     *
     * @abstract
     * @return void
     */
    abstract function getAbsolutePath(Media $media);

    /**
     *
     * @abstract
     * @param  $media
     * @return void
     */
    abstract function postUpdate(Media $media);

    /**
     *
     * @abstract
     * @param  $media
     * @return void
     */
    abstract function postRemove(Media $media);

    /**
     *
     * @abstract
     * @param  $media
     * @return void
     */
    abstract function postPersist(Media $media);

    abstract function getHelperProperties(Media $media, $format);
    
    public function generatePrivatePath(Media $media)
    {
        $limit_first_level = 100000;
        $limit_second_level = 1000;

        $rep_first_level = (int) ($media->getId() / $limit_first_level);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $limit_first_level)) / $limit_second_level);
        $path = sprintf('%s/%04s/%02s',
            $this->settings['private_path'],
            $rep_first_level + 1,
            $rep_second_level + 1
        );

        return $path;
    }

    public function generatePublicPath(Media $media)
    {

        $limit_first_level = 100000;
        $limit_second_level = 1000;

        $rep_first_level = (int) ($media->getId() / $limit_first_level);
        $rep_second_level = (int) (($media->getId() - ($rep_first_level * $limit_first_level)) / $limit_second_level);
        $path = sprintf('%s/%04s/%02s', // todo : allow this to be configured....
            $this->settings['public_path'],
            $rep_first_level + 1,
            $rep_second_level + 1
        );

        return $path;
    }

    public function buildDirectory(Media $media)
    {
        $path = $this->generatePrivatePath($media);

        if (!is_dir($path)) {
            if (!@mkdir($path, 0755, true)) {
                throw new \RuntimeException('unable to create directory : ' . $path);
            }
        }

        return $path;
    }

    public function generatePublicUrl(Media $media, $format)
    {

        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePublicPath($media),
            $media->getId(),
            $format
        );
    }

    public function generatePrivateUrl(Media $media, $format)
    {

        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePrivatePath($media),
            $media->getId(),
            $format
        );
    }

    
    public function getFormats()
    {

        return $this->formats;
    }
}
