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
     *
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract protected function recomputeSingleEntityChangeSet(EventArgs $args);

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    abstract protected function getProvider(EventArgs $args); 

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function postUpdate(EventArgs $args);

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function postRemove(EventArgs $args);

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function postPersist(EventArgs $args);

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function preUpdate(EventArgs $args);

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function preRemove(EventArgs $args);

    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
     */
    abstract public function prePersist(EventArgs $args);
}
