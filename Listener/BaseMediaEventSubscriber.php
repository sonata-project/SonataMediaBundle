<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    public function getPool()
    {
        return $this->container->get('sonata.media.pool');
    }

    /**
     * @abstract
     * @param \Doctrine\Common\EventArgs $args
     * @return void
     */
    abstract protected function recomputeSingleEntityChangeSet(EventArgs $args);

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    protected function getProvider(EventArgs $args)
    {
        $media = $args->getEntity();

        if(!$media instanceof MediaInterface) {
            return null;
        }

        return $this->getPool()->getProvider($media->getProviderName());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function postUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postUpdate($args->getEntity());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function postRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postRemove($args->getEntity());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function postPersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postPersist($args->getEntity());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function preUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($args->getEntity());
        $provider->preUpdate($args->getEntity());

        $this->recomputeSingleEntityChangeSet($args);
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function preRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->preRemove($args->getEntity());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return
     */
    public function prePersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($args->getEntity());
        $provider->prePersist($args->getEntity());
    }
}
