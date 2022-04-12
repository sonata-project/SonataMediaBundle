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

namespace Sonata\MediaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;

/**
 * @phpstan-template T of ObjectManager
 */
abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    protected Pool $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function postUpdate(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postUpdate($media);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function postRemove(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postRemove($media);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function postPersist(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postPersist($media);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function preUpdate(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $provider = $this->getProvider($media);

        $provider->transform($media);
        $provider->preUpdate($media);

        $this->recomputeSingleEntityChangeSet($args);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function preRemove(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->preRemove($media);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    final public function prePersist(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $provider = $this->getProvider($media);

        $provider->transform($media);
        $provider->prePersist($media);
    }

    /**
     * @param LifecycleEventArgs<T> $args
     */
    abstract protected function recomputeSingleEntityChangeSet(LifecycleEventArgs $args): void;

    /**
     * @param LifecycleEventArgs<T> $args
     */
    abstract protected function getMedia(LifecycleEventArgs $args): ?MediaInterface;

    final protected function getProvider(MediaInterface $media): MediaProviderInterface
    {
        return $this->pool->getProvider($media->getProviderName());
    }
}
