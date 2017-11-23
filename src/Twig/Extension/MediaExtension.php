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
use Sonata\MediaBundle\Provider\MediaProviderInterface;
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

        $provider = $this->getMediaService()->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);
        $formatOptions = $provider->getFormat($format);

        $options = array_merge(
            false !== $formatOptions ? $formatOptions : [],
            [
                'title' => $media->getName(),
                'alt' => $media->getName(),
                'src' => $provider->generatePublicUrl($media, $format),
            ],
            $options
        );

        if (isset($options['width']) && !isset($options['sizes'])) {
            $options['sizes'] = sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $options['width']);
        }

        if (MediaProviderInterface::FORMAT_ADMIN !== $format) {
            $srcSetFormats = $provider->getFormats();

            if (isset($options['srcset']) && is_array($options['srcset'])) {
                $srcSetFormats = [];
                foreach ($options['srcset'] as $srcSetFormat) {
                    $formatName = $provider->getFormatName($media, $srcSetFormat);
                    $srcSetFormats[$formatName] = $provider->getFormat($formatName);
                }
                unset($options['srcset']);
            }

            if (!isset($options['srcset'])) {
                $srcSet = [];

                foreach ($srcSetFormats as $providerFormat => $settings) {
                    if (0 === strpos($providerFormat, $media->getContext()) && isset($settings['width'])) {
                        $srcSet[] = sprintf('%s %dw', $provider->generatePublicUrl($media, $providerFormat), $settings['width']);
                    }
                }

                if (isset($options['width'])) {
                    $srcSet[] = sprintf(
                        '%s %dw',
                        $provider->generatePublicUrl($media, MediaProviderInterface::FORMAT_REFERENCE),
                        $options['width']
                    );
                }

                $options['srcset'] = implode(', ', $srcSet);
            }
        }

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
