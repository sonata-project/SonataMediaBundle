<?php

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

class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
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

        $path = $provider->getCdnPath($path, $media->getCdnIsFlushable());

        return $this->cacheManager->getBrowserPath($path, $format);
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
