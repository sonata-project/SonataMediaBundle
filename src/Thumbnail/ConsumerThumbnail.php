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
use Sonata\NotificationBundle\Backend\BackendInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ConsumerThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var ThumbnailInterface
     */
    private $thumbnail;

    /**
     * @var BackendInterface
     */
    private $backend;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(string $id, ThumbnailInterface $thumbnail, BackendInterface $backend, EventDispatcherInterface $dispatcher)
    {
        $this->id = $id;
        $this->thumbnail = $thumbnail;
        $this->backend = $backend;
        $this->dispatcher = $dispatcher;
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    public function generate(MediaProviderInterface $provider, MediaInterface $media): void
    {
        $backend = $this->backend;
        $id = $this->id;

        $publish = static function () use ($backend, $media, $id): void {
            $backend->createAndPublish('sonata.media.create_thumbnail', [
                'thumbnailId' => $id,
                'mediaId' => $media->getId(),

                // force this value as the message is sent inside a transaction,
                // so we have a race condition
                'providerReference' => $media->getProviderReference(),
            ]);
        };

        $this->dispatcher->addListener('kernel.finish_request', $publish);
        $this->dispatcher->addListener('console.terminate', $publish);
    }

    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        return $this->thumbnail->delete($provider, $media, $formats);
    }
}
