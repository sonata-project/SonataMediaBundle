<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Twig\Extension;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Environment;
use Twig\Extension\AbstractExtension;

final class MediaExtension extends AbstractExtension
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
     * @var Environment
     */
    protected $twig;

    public function __construct(Pool $mediaService, ManagerInterface $mediaManager, Environment $twig)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
        $this->twig = $twig;
    }

    public function getTokenParsers()
    {
        return [
            new MediaTokenParser(static::class),
            new ThumbnailTokenParser(static::class),
            new PathTokenParser(static::class),
        ];
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

        if (null === $media) {
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

        if (null === $media) {
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

        if (\is_array($format_definition) && $format_definition['width']) {
            $defaultOptions['width'] = $format_definition['width'];
        }
        if (\is_array($format_definition) && $format_definition['height']) {
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
     *
     * @return mixed
     */
    public function render($template, array $parameters = [])
    {
        if (!isset($this->resources[$template])) {
            $this->resources[$template] = $this->twig->load($template);
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
     */
    private function getMedia($media): ?MediaInterface
    {
        if (!$media instanceof MediaInterface && \strlen((string) $media) > 0) {
            $media = $this->mediaManager->findOneBy([
                'id' => $media,
            ]);
        }

        if (!$media instanceof MediaInterface) {
            return null;
        }

        if (MediaInterface::STATUS_OK !== $media->getProviderStatus()) {
            return null;
        }

        return $media;
    }
}
