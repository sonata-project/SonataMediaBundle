<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener\ODM;

use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Events;
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
     * @param \Doctrine\ODM\Event\LifecycleEventArgs $args
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    protected function getProvider(EventArgs $args)
    {
      $media = $args->getDocument();
    
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
    
      $provider->postUpdate($args->getDocument());
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
    
      $provider->postRemove($args->getDocument());
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
    
      $provider->postPersist($args->getDocument());
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
    
      $provider->transform($args->getDocument());
      $provider->preUpdate($args->getDocument());
    
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
    
      $provider->preRemove($args->getDocument());
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
    
      $provider->transform($args->getDocument());
      $provider->prePersist($args->getDocument());
    }
    /**
     * @param \Doctrine\Common\EventArgs $args
     * @return void
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        $em = $args->getDocumentManager();

        $em->getUnitOfWork()->recomputeSingleDocumentChangeSet(
            $em->getClassMetadata(get_class($args->getDocument())),
            $args->getDocument()
        );
    }
}
