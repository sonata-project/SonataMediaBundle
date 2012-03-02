<?php

/*
 * This file is part of sonata-project.
 *
 * (c) 2010 Thomas Rabaix
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Extension;

use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaExtension extends \Twig_Extension
{
    protected $mediaService;

    protected $ressources = array();

    protected $mediaManager;

    public function __construct(Pool $mediaService, MediaManagerInterface $mediaManager)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
    }

    /**
     * @return array
     */
    public function getTokenParsers()
    {
        return array(
            new MediaTokenParser($this->getName()),
            new ThumbnailTokenParser($this->getName()),
            new PathTokenParser($this->getName()),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'sonata_media';
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @param array $options
     * @return string
     */
    public function media($media = null, $format, $options = array())
    {
        $media = $this->getMedia($media);

        if (!$media) {
            return '';
        }

        $provider = $this
            ->getMediaService()
            ->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        $options = $provider->getHelperProperties($media, $format, $options);

        return $this->render($provider->getTemplate('helper_view'), array(
            'media'    => $media,
            'format'   => $format,
            'options'  => $options,
        ));
    }

    /**
     * @param $media
     * @return null|\Sonata\MediaBundle\Model\MediaInterface
     */
    private function getMedia($media)
    {
        if ($media instanceof MediaInterface) {
            return $media;
        }

        if (strlen($media) > 0) {
            $media = $this->mediaManager->findOneBy(array(
                'id' => $media
            ));
        }

        return $media;
    }

    /**
     * Returns the thumbnail for the provided media
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @param array $options
     * @return string
     */
    public function thumbnail($media = null, $format, $options = array())
    {
        $media = $this->getMedia($media);

        if (!$media) {
            return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);
        $format_definition = $provider->getFormat($format);

        // build option
        $defaultOptions = array(
            'title' => $media->getName(),
        );

        if ($format_definition['width']) {
            $defaultOptions['width'] = $format_definition['width'];
        }
        if ($format_definition['height']) {
            $defaultOptions['height'] = $format_definition['height'];
        }

        $options = array_merge($defaultOptions, $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->render($provider->getTemplate('helper_thumbnail'), array(
            'media'    => $media,
            'options'  => $options,
        ));
    }

    public function render($template, array $parameters = array())
    {
        if (!isset($this->ressources[$template])) {
            $this->ressources[$template] = $this->environment->loadTemplate($template);
        }

        return $this->ressources[$template]->render($parameters);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function path($media = null, $format)
    {
        $media = $this->getMedia($media);

        if (!$media) {
             return '';
        }

        $provider = $this->getMediaService()
           ->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }

    /**
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    public function getMediaService()
    {
        return $this->mediaService;
    }
}