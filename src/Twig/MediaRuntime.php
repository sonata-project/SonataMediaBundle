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

namespace Sonata\MediaBundle\Twig;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

final class MediaRuntime implements RuntimeExtensionInterface
{
    private const EXCLUDED_FORMATS = ['svg'];

    private Pool $pool;

    private MediaManagerInterface $mediaManager;

    private Environment $twig;

    public function __construct(
        Pool $pool,
        MediaManagerInterface $mediaManager,
        Environment $twig
    ) {
        $this->pool = $pool;
        $this->mediaManager = $mediaManager;
        $this->twig = $twig;
    }

    /**
     * @param int|string|MediaInterface $media
     * @param array<string, mixed>      $options
     */
    public function media($media, string $format, array $options = []): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);
        $options = $provider->getHelperProperties($media, $format, $options);
        $template = $provider->getTemplate('helper_view');

        if (null === $template) {
            return '';
        }

        return $this->twig->render($template, [
            'media' => $media,
            'format' => $format,
            'options' => $options,
        ]);
    }

    /**
     * Returns the thumbnail for the provided media.
     *
     * @param int|string|MediaInterface $media
     * @param array<string, mixed>      $options
     */
    public function thumbnail($media, string $format, array $options = []): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->pool->getProvider($media->getProviderName());
        $format = $provider->getFormatName($media, $format);
        $formatDefinition = $provider->getFormat($format);
        $template = $provider->getTemplate('helper_thumbnail');

        if (null === $template) {
            return '';
        }

        // build option
        $defaultOptions = [
            'title' => $media->getName(),
            'alt' => $media->getName(),
        ];

        if (false !== $formatDefinition && isset($formatDefinition['width'])) {
            $defaultOptions['width'] = $formatDefinition['width'];
        }
        if (false !== $formatDefinition && isset($formatDefinition['height'])) {
            $defaultOptions['height'] = $formatDefinition['height'];
        }

        $options = array_merge($defaultOptions, $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->twig->render($template, [
            'media' => $media,
            'options' => $options,
        ]);
    }

    /**
     * @param int|string|MediaInterface $media
     */
    public function path($media, string $format): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }

    /**
     * @param int|string|MediaInterface $media
     */
    private function getMedia($media): ?MediaInterface
    {
        if (!\is_int($media) && !\is_string($media) && !$media instanceof MediaInterface) {
            throw new \TypeError(sprintf(
                'Media parameter must be either an identifier or the media itself for Twig functions, "%s" given.',
                \is_object($media) ? 'instance of '.\get_class($media) : \gettype($media)
            ));
        }

        if (!$media instanceof MediaInterface) {
            $media = $this->mediaManager->find($media);
        }

        if (!$media instanceof MediaInterface) {
            return null;
        }

        
        $fileName = $media->getName();
        if (
            MediaInterface::STATUS_OK !== $media->getProviderStatus() &&
            null !== $fileName &&  \in_array(strtolower(pathinfo($fileName, \PATHINFO_EXTENSION)), self::EXCLUDED_FORMATS, true)
        ) {
            return null;
        }

        return $media;
    }
}
