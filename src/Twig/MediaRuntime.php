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
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Environment
     */
    private $twig;

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
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
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
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
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
     * @param int|string|MediaInterface|null $media
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
     * @param int|string|MediaInterface|null $media
     */
    private function getMedia($media): ?MediaInterface
    {
        // NEXT_MAJOR: Throw exception instead and remove references to null media being accepted.
        if (!\is_int($media) && !\is_string($media) && !$media instanceof MediaInterface) {
            @trigger_error(
                'Using SonataMediaBundle custom Twig functions with a media that is not'
                .' the identifier of the media or the media itself is deprecated since'
                .' sonata-project/media-bundle 3.x and will be removed in version 4.0.',
                \E_USER_DEPRECATED
            );
            // throw new \TypeError(sprintf(
            //     'Media parameter must be either an identifier or the media itself for Twig functions, %s given.',
            //     $media
            // ));
        }

        if (!$media instanceof MediaInterface && null !== $media) {
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
