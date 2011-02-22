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
        
        $options = $this
            ->getMediaService()
            ->getProvider($media->getProviderName())
            ->getHelperProperties($media, $format, $options, $this->getMediaService()->getSettings());

        return $this->getTemplating()->render(
            sprintf('SonataMediaBundle:Provider:view_%s.html.twig', $media->getProviderName()),
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

         // compute the cdn option
         $settings = $this->getMediaService()->getSettings();
         $base_media = $settings['cdn_enabled'] ? $settings['cdn_path'] : '';

         // the media is flushable, so we are working with a recent version not yet handled by the cdn
         if ($media->getCdnIsFlushable()) {
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

         $options['src'] = sprintf('%s%s', $base_media, $provider->generatePublicUrl($media, $format));

         return $this->getTemplating()->render(
            sprintf('SonataMediaBundle:Provider:thumbnail.html.twig', $media->getProviderName()),
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
