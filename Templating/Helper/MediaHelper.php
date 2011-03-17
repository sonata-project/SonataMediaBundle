<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;


/**
 * MediaHelper manages action inclusions.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.com>
 */
class MediaHelper extends Helper
{

    protected $mediaService = null;

    protected $templating = null;

    /**
     * Constructor.
     *
     * @param Constructor $media_provider A MediaProvider instance
     */
    public function __construct($mediaService, $templating)
    {
        $this->mediaService    = $mediaService;
        $this->templating       = $templating;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'sonata_media';
    }

    /**
     *
     * return the provider view for the provided media
     *
     * @param Media $media
     * @param string $format
     * @param array $options
     * @return string
     */
    public function media($media, $format, $options = array())
    {

        if (!$media) {
            return '';
        }

        $provider = $this
            ->getMediaService()
            ->getProvider($media->getProviderName());
        
        $options = $provider->getHelperProperties($media, $format, $options);

        return $this->getTemplating()->render(
            $provider->getTemplate('helper_view'),
            array(
                 'media'    => $media,
                 'format'   => $format,
                 'options'  => $options,
            )
        );
    }

    /**
     * return the thumbnail for the provided media
     *
     * @param Media $media
     * @param string $format
     * @param array $options
     * @return string
     */
    public function thumbnail($media, $format, $options = array())
    {

         if (!$media) {
             return '';
         }

         $provider = $this->getMediaService()
            ->getProvider($media->getProviderName());

         $format_definition = $provider->getFormat($format);

         // build option
         $options = array_merge(array(
             'title' => $media->getName(),
             'width' => $format_definition['width'],
         ), $options);

         $options['src'] = $provider->generatePublicUrl($media, $format);

         return $this->getTemplating()->render(
            $provider->getTemplate('helper_thumbnail'),
            array(
                 'media'    => $media,
                 'options'  => $options,
            )
         );
     }


    public function setMediaService($mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function getMediaService()
    {
        return $this->mediaService;
    }

    public function setTemplating($templating)
    {
        $this->templating = $templating;
    }

    public function getTemplating()
    {
        return $this->templating;
    }
}
