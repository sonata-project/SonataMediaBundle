<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener\ORM;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;

class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,
        );
    }
    
    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */    
    protected function getProvider(EventArgs $args)
    {
        $media = $args->getEntity();

        if (!$media instanceof MediaInterface) {
            return null;
        }

        return $this->getPool()->getProvider($media->getProviderName());
    }
    
    /**
     * @param \Doctrine\Common\EventArgs $args
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function prePersist(EventArgs $args)
    {
        if (!($provider = $this->getProvider($args))) {
            return;
        }
    
        $provider->transform($args->getEntity());
        $provider->prePersist($args->getEntity());
    }

    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return void
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        $em = $args->getEntityManager();

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(get_class($args->getEntity())),
            $args->getEntity()
        );
    }
}