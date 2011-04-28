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
        $this->templating      = $templating;
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
     * Returns the provider view for the provided media
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

        $format = $provider->getFormatName($media, $format);

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
     * Returns the thumbnail for the provided media
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

         $format = $provider->getFormatName($media, $format);
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

    /**
     * @param Media $media
     * @param string $format
     * @return string
     */
    public function path($media, $format)
    {
        if (!$media) {
             return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());

        return $provider->generatePublicUrl($media, $format);
    }

    public function getMediaService()
    {
        return $this->mediaService;
    }

    public function getTemplating()
    {
        return $this->templating;
    }
}
