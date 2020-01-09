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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @deprecated since sonata-project/media-bundle 3.3, will be removed in 4.0.
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @param RouterInterface|CacheManager $cacheManager
     */
    public function __construct($cacheManager)
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
        if (MediaProviderInterface::FORMAT_REFERENCE !== $format) {
            throw new \RuntimeException('No private url for LiipImagineThumbnail');
        }

        $path = $provider->getReferenceImage($media);

        return $path;
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
        // feature not available
    }
}
