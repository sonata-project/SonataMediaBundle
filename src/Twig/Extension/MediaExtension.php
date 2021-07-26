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
use Twig\TemplateWrapper;

final class MediaExtension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $mediaPool;

    /**
     * @var array<string, TemplateWrapper>
     */
    private $resources = [];

    /**
     * @var ManagerInterface<MediaInterface>
     */
    private $mediaManager;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @param ManagerInterface<MediaInterface> $mediaManager
     */
    public function __construct(Pool $mediaPool, ManagerInterface $mediaManager, Environment $twig)
    {
        $this->mediaPool = $mediaPool;
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
     * @param MediaInterface|int|string $media
     * @param array<string, mixed>      $options
     */
    public function media($media, string $format, array $options = []): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);
        $options = $provider->getHelperProperties($media, $format, $options);
        $template = $provider->getTemplate('helper_view');

        if (null === $template) {
            return '';
        }

        return $this->render($template, [
            'media' => $media,
            'format' => $format,
            'options' => $options,
        ]);
    }

    /**
     * Returns the thumbnail for the provided media.
     *
     * @param MediaInterface|int|string $media
     * @param array<string, mixed>      $options
     */
    public function thumbnail($media, string $format, array $options = []): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->mediaPool->getProvider($media->getProviderName());

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

        if (false !== $formatDefinition && null !== $formatDefinition['width']) {
            $defaultOptions['width'] = $formatDefinition['width'];
        }
        if (false !== $formatDefinition && null !== $formatDefinition['height']) {
            $defaultOptions['height'] = $formatDefinition['height'];
        }

        $options = array_merge($defaultOptions, $options);

        $options['src'] = $provider->generatePublicUrl($media, $format);

        return $this->render($template, [
            'media' => $media,
            'options' => $options,
        ]);
    }

    /**
     * @param MediaInterface|int|string $media
     */
    public function path($media, string $format): string
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $format = $provider->getFormatName($media, $format);

        return $provider->generatePublicUrl($media, $format);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function render(string $template, array $parameters = []): string
    {
        if (!isset($this->resources[$template])) {
            $this->resources[$template] = $this->twig->load($template);
        }

        return $this->resources[$template]->render($parameters);
    }

    /**
     * @param MediaInterface|int|string $media
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
