<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Extension;

use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;

class MediaExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /**
     * @var Pool
     */
    protected $mediaService;

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @param Pool             $mediaService
     * @param ManagerInterface $mediaManager
     */
    public function __construct(Pool $mediaService, ManagerInterface $mediaManager)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new MediaTokenParser(get_called_class()),
            new ThumbnailTokenParser(get_called_class()),
            new PathTokenParser(get_called_class()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function media($media, $format, $options = [])
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

        return $this->render($provider->getTemplate('helper_view'), [
            'media' => $media,
            'format' => $format,
            'options' => $options,
        ]);
    }

    /**
     * Returns the thumbnail for the provided media.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = [])
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
        $defaultOptions = [
            'title' => $media->getName(),
            'alt' => $media->getName(),
        ];

        if ($format_definition['width']) {
            $defaultOptions['width'] = $format_definition['width'];
        }
        if ($format_definition['height']) {
            $defaultOptions['height'] = $format_definition['height'];
        }

        $options = array_merge($defaultOptions, $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->render($provider->getTemplate('helper_thumbnail'), [
            'media' => $media,
            'options' => $options,
        ]);
    }

    /**
     * @param string $template
     * @param array  $parameters
     *
     * @return mixed
     */
    public function render($template, array $parameters = [])
    {
        if (!isset($this->resources[$template])) {
            $this->resources[$template] = $this->environment->loadTemplate($template);
        }

        return $this->resources[$template]->render($parameters);
    }

    /**
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function path($media, $format)
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
     * @return Pool
     */
    public function getMediaService()
    {
        return $this->mediaService;
    }

    /**
     * @param mixed $media
     *
     * @return MediaInterface|null|bool
     */
    private function getMedia($media)
    {
        if (!$media instanceof MediaInterface && strlen($media) > 0) {
            $media = $this->mediaManager->findOneBy([
                'id' => $media,
            ]);
        }

        if (!$media instanceof MediaInterface) {
            return false;
        }

        if (MediaInterface::STATUS_OK !== $media->getProviderStatus()) {
            return false;
        }

        return $media;
    }
}
