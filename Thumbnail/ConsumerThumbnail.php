<?php

/*
 * This file is part of the Sonata project.
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

class ConsumerThumbnail implements ThumbnailInterface
{
    protected $id;

    protected $thumbnail;

    protected $backend;

    /**
     * @param string                                              $id
     * @param ThumbnailInterface                                  $thumbnail
     * @param \Sonata\NotificationBundle\Backend\BackendInterface $backend
     */
    public function __construct($id, ThumbnailInterface $thumbnail, BackendInterface $backend)
    {
        $this->id        = $id;
        $this->thumbnail = $thumbnail;
        $this->backend   = $backend;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($provider, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        $this->backend->createAndPublish('sonata.media.create_thumbnail', array(
            'thumbnailId'       => $this->id,
            'mediaId'           => $media->getId(),

            // force this value as the message is sent inside a transaction,
            // so we have a race condition
            'providerReference' => $media->getProviderReference(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media)
    {
        return $this->thumbnail->delete($provider, $media);
    }
}
