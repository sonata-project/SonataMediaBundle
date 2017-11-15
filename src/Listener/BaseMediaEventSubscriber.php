<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->container->get('sonata.media.pool');
    }

    /**
     * @param EventArgs $args
     */
    public function postUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postUpdate($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function postRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postRemove($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function postPersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->postPersist($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function preUpdate(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->preUpdate($this->getMedia($args));

        $this->recomputeSingleEntityChangeSet($args);
    }

    /**
     * @param EventArgs $args
     */
    public function preRemove(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->preRemove($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    public function prePersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }

        $provider->transform($this->getMedia($args));
        $provider->prePersist($this->getMedia($args));
    }

    /**
     * @param EventArgs $args
     */
    abstract protected function recomputeSingleEntityChangeSet(EventArgs $args);

    /**
     * @param EventArgs $args
     *
     * @return \Sonata\MediaBundle\Model\MediaInterface
     */
    abstract protected function getMedia(EventArgs $args);

    /**
     * @param EventArgs $args
     *
     * @return MediaProviderInterface
     */
    protected function getProvider(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (!$media instanceof MediaInterface) {
            return;
        }

        return $this->getPool()->getProvider($media->getProviderName());
    }
}
