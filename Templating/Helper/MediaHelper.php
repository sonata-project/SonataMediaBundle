<?php

namespace Bundle\MediaBundle\Templating\Helper;

use Symfony\Component\Templating\Helper\Helper;

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ActionsHelper manages action inclusions.
 *
 * @author Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class MediaHelper extends Helper
{

    protected $media_service = null;

    protected $templating = null;

    /**
     * Constructor.
     *
     * @param Constructor $media_provider A MediaProvider instance
     */
    public function __construct($media_service, $templating)
    {
        $this->media_service    = $media_service;
        $this->templating       = $templating;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'media';
    }

    /**
     *
     * return the provider view for the provided media
     *
     * @param  $media
     * @param  $format
     * @param array $options
     * @return string
     */
    public function media($media, $format, $options = array())
    {

        if(!$media) {
            return '';
        }
        
        $options = $this
            ->getMediaService()
            ->getProvider($media->getProviderName())
            ->getHelperProperties($media, $format, $options, $this->getMediaService()->getSettings());

        return $this->getTemplating()->render(
            sprintf('MediaBundle:Provider:view_%s.twig', $media->getProviderName()),
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
     * @param  $media
     * @param  $format
     * @param array $options
     * @return string
     */
    public function thumbnail($media, $format, $options = array())
    {

         if(!$media) {
             return '';
         }

         // compute the cdn option
         $settings = $this->getMediaService()->getSettings();
         $base_media = $settings['cdn_enabled'] ? $settings['cdn_path'] : '';

         // the media is flushable, so we are working with a recent version not yet handled by the cdn
         if($media->getCdnIsFlushable()) {
             $base_media = '';
         }


         $provider = $this
            ->getMediaService()
            ->getProvider($media->getProviderName());

         $format_definition = $provider->getFormat($format);

         // build option
         $options = array_merge(array(
             'title' => $media->getName(),
             'width' => $format_definition['width'],
         ), $options);

         $options['src'] = sprintf('%s/%s', $base_media, $provider->generatePublicUrl($media, $format));

         return $this->getTemplating()->render(
            sprintf('MediaBundle:Provider:thumbnail.twig', $media->getProviderName()),
            array(
                 'media'    => $media,
                 'options'  => $options,
            )
         );
     }


    public function setMediaService($media_service)
    {
        $this->media_service = $media_service;
    }

    public function getMediaService()
    {
        return $this->media_service;
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
