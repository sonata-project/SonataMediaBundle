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
    private Packages $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_ADMIN !== $format) {
            throw new \InvalidArgumentException(sprintf('Unsupported format "%s".', $format));
        }

        return $this->packages->getUrl('bundles/sonatamedia/file.png');
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        throw new \BadMethodCallException('Unable to generate private thumbnail url for media files.');
    }
}
