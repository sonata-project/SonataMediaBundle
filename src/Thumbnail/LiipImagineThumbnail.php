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

final class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        $path = $provider->getReferenceImage($media);

        if (MediaProviderInterface::FORMAT_ADMIN === $format || MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $path;
        }

        $path = $provider->getCdnPath($path, $media->getCdnIsFlushable());

        return $this->cacheManager->getBrowserPath($path, $format);
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_REFERENCE !== $format) {
            throw new \RuntimeException('No private url for LiipImagineThumbnail');
        }

        return $provider->getReferenceImage($media);
    }

    public function generate(MediaProviderInterface $provider, MediaInterface $media): void
    {
        // nothing to generate, as generated on demand
    }

    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null): void
    {
        // feature not available
    }
}
