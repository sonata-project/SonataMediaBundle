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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Sonata\NotificationBundle\Exception\HandlingException;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CreateThumbnailConsumer implements ConsumerInterface
{
    /**
     * @var ManagerInterface<MediaInterface>
     */
    private $mediaManager;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ManagerInterface<MediaInterface> $mediaManager
     */
    public function __construct(ManagerInterface $mediaManager, Pool $pool, ContainerInterface $container)
    {
        $this->mediaManager = $mediaManager;
        $this->pool = $pool;
        $this->container = $container;
    }

    public function process(ConsumerEvent $event): void
    {
        $media = $this->mediaManager->findOneBy([
            'id' => $event->getMessage()->getValue('mediaId'),
        ]);

        if (null === $media) {
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

    private function getThumbnail(ConsumerEvent $event): ThumbnailInterface
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
