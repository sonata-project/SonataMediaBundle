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

namespace Sonata\MediaBundle\Thumbnail;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Sonata\MediaBundle\LiipImagine\ResolverRegistryInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Routing\RouterInterface;

class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @deprecated Since version 3.3, will be removed in 4.0.
     *
     * @var RouterInterface|null
     */
    protected $router;

    /**
     * @var CacheManager|null
     */
    private $cacheManager;
    /**
     * @var ResolverRegistryInterface|null
     */
    private $resolverRegistry;

    /**
     * @param RouterInterface|CacheManager   $cacheManager
     * @param ResolverRegistryInterface|null $resolverRegistry
     */
    public function __construct($cacheManager, ResolverRegistryInterface $resolverRegistry = null)
    {
        if ($cacheManager instanceof RouterInterface) {
            @trigger_error(sprintf(
                'Using an instance of %s is deprecated since version 3.3 and will be removed in 4.0. Use %s.',
                RouterInterface::class,
                CacheManager::class
            ), E_USER_DEPRECATED);
            $this->router = $cacheManager;
        }
        $this->cacheManager = $cacheManager;
        if (!$resolverRegistry instanceof ResolverRegistryInterface) {
            @trigger_error(sprintf(
                'Using %s without a %s is deprecated since version 3.16 and will no longer be possible in 4.0.',
                __CLASS__,
                ResolverRegistryInterface::class
            ), E_USER_DEPRECATED);
        }
        $this->resolverRegistry = $resolverRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        $path = $provider->getReferenceImage($media);

        if (MediaProviderInterface::FORMAT_ADMIN === $format || MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $path;
        }
        if ($this->router instanceof RouterInterface && !($this->cacheManager instanceof CacheManager)) {
            $path = $this->router->generate(
                sprintf('_imagine_%s', $format),
                ['path' => sprintf('%s/%s_%s.jpg', $provider->generatePath($media), $media->getId(), $format)]
            );
        }

        $path = $provider->getCdnPath($path, $media->getCdnIsFlushable());
        if ($this->cacheManager instanceof CacheManager) {
            $path = $this->cacheManager->getBrowserPath($path, $format);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        $path = $provider->getReferenceImage($media);
        if (MediaProviderInterface::FORMAT_ADMIN === $format || MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $path;
        }
        if (!$this->resolverRegistry instanceof ResolverRegistryInterface) {
            throw new \RuntimeException(sprintf(
                'Cannot generate private url for LiipImagine, use the "%s" added in 3.16 to add support.',
                ResolverRegistryInterface::class
            ));
        }

        return $this->resolverRegistry->getResolver($format)->resolve($path, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        // nothing to generate, as generated on demand
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        $path = $provider->getReferenceImage($media);
        foreach ((array) ($formats ?: array_keys($provider->getFormats())) as $format) {
            $this->resolverRegistry->getResolver($format)->remove([$path], [$format]);
        }
    }
}
