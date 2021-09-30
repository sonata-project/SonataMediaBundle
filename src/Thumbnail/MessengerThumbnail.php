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

use Sonata\MediaBundle\Messenger\GenerateThumbnailsMessage;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerThumbnail implements ThumbnailInterface
{
    /**
     * @var ThumbnailInterface
     */
    private $thumbnail;

    /**
     * @var MessageBusInterface
     */
    private $bus;

    public function __construct(ThumbnailInterface $thumbnail, MessageBusInterface $bus)
    {
        $this->thumbnail = $thumbnail;
        $this->bus = $bus;
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePublicUrl($provider, $media, $format);
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        $mediaId = $media->getId();

        if (null === $mediaId) {
            throw new \RuntimeException('Cannot generate thumbnails for media without id.');
        }

        $this->bus->dispatch(new GenerateThumbnailsMessage($mediaId));
    }

    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        return $this->thumbnail->delete($provider, $media, $formats);
    }
}
