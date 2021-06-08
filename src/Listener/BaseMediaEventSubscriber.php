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
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postUpdate($this->getMedia($args));
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postRemove($this->getMedia($args));
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postPersist($this->getMedia($args));
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->preUpdate($this->getMedia($args));

        $this->recomputeSingleEntityChangeSet($args);
    }

    public function preRemove(LifecycleEventArgs $args): void
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->preRemove($this->getMedia($args));
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->prePersist($this->getMedia($args));
    }

    abstract protected function recomputeSingleEntityChangeSet(LifecycleEventArgs $args);

    /**
     * @throws \RuntimeException
     *
     * @return MediaInterface
     */
    abstract protected function getMedia(LifecycleEventArgs $args);

    /**
     * @return MediaProviderInterface
     */
    protected function getProvider(LifecycleEventArgs $args)
    {
        return $this->pool->getProvider($this->getMedia($args)->getProviderName());
    }
}
