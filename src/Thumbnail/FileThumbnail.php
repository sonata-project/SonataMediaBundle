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

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Asset\Packages;

/**
 * @author Jordi Sala Morales <jordism91@gmail.com>
 */
final class FileThumbnail implements ThumbnailInterface
{
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_ADMIN !== $format) {
            throw new \RuntimeException(sprintf('Unable to generate thumbnail for the given format %s.', $format));
        }

        return $this->packages->getUrl('bundles/sonatamedia/file.png');
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        throw new \RuntimeException('Unable to generate private thumbnail url for media files.');
    }

    public function generate(MediaProviderInterface $provider, MediaInterface $media): void
    {
        // nothing to generate, as the thumbnails for files are already generated
    }

    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null): void
    {
        // feature not available
    }
}
