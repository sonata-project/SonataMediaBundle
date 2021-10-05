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

namespace Sonata\MediaBundle\Consumer;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Exception\HandlingException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * NEXT_MAJOR: remove this file.
 *
 * @deprecated since sonata-project/media-bundle 3.34, to be removed in 4.0.
 */
class CreateThumbnailConsumer implements ConsumerInterface
{
    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ManagerInterface $mediaManager, Pool $pool, ContainerInterface $container)
    {
        $this->mediaManager = $mediaManager;
        $this->pool = $pool;
        $this->container = $container;
    }

    public function process(ConsumerEvent $event)
    {
        $media = $this->mediaManager->findOneBy([
            'id' => $event->getMessage()->getValue('mediaId'),
        ]);

        if (!$media) {
            throw new HandlingException(sprintf('Media not found for identifier: %s.', $event->getMessage()->getValue('mediaId')));
        }

        // solve race condition between message queue and database transaction
        $media->setProviderReference($event->getMessage()->getValue('providerReference'));

        try {
            $this->getThumbnail($event)->generate($this->pool->getProvider($media->getProviderName()), $media);
        } catch (\LogicException $e) {
            throw new HandlingException(
                sprintf('Error while generating exception for media.id: %s', $event->getMessage()->getValue('mediaId')),
                0,
                $e
            );
        }
    }

    /**
     * @return ThumbnailInterface
     */
    protected function getThumbnail(ConsumerEvent $event)
    {
        $thumbnail = $this->container->get($event->getMessage()->getValue('thumbnailId'));

        if (!$thumbnail instanceof ThumbnailInterface) {
            throw new HandlingException(sprintf(
                'Invalid thumbnail instance requested - id: %s',
                $event->getMessage()->getValue('thumbnailId')
            ));
        }

        return $thumbnail;
    }
}
