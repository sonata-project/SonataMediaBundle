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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;

abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    /**
     * @var Pool
     */
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postUpdate($media);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postRemove($media);
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->postPersist($media);
    }

    public function preUpdate(LifecycleEventArgs $args): void
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

    public function preRemove(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($media)->preRemove($media);
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $provider = $this->getProvider($media);

        $provider->transform($media);
        $provider->prePersist($media);
    }

    abstract protected function recomputeSingleEntityChangeSet(LifecycleEventArgs $args): void;

    abstract protected function getMedia(LifecycleEventArgs $args): ?MediaInterface;

    protected function getProvider(MediaInterface $media): MediaProviderInterface
    {
        return $this->pool->getProvider($media->getProviderName());
    }
}
