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
    public function __construct(
        private Pool $pool,
        private MediaManagerInterface $mediaManager,
        private Environment $twig
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function media(int|string|MediaInterface $media, string $format, array $options = []): string
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
     * @param array<string, mixed> $options
     */
    public function thumbnail(int|string|MediaInterface $media, string $format, array $options = []): string
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

    public function path(int|string|MediaInterface $media, string $format): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }

    private function getMedia(int|string|MediaInterface $media): ?MediaInterface
    {
        if (!$media instanceof MediaInterface) {
            $media = $this->mediaManager->find($media);
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
