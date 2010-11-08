<?php

namespace Bundle\MediaBundle\Provider;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;

class Service implements EventSubscriber {

    protected
        $providers = array(),
        $settings = array();


    public function getProvider($name) {

        if(!isset($this->providers[$name])) {
            throw new \RuntimeException(sprintf('unable to retrieve the provider named : %s', $name));
        }

        return $this->providers[$name];
    }

    public function addProvider($name, $instance) {
        $this->providers[$name] = $instance;
    }

    public function getSubscribedEvents() {

        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,

        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->postUpdate($entity);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->postRemove($entity);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->postPersist($entity);

    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->preUpdate($entity);
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->preRemove($entity);
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if(!$entity instanceof \Bundle\MediaBundle\Entity\BaseMedia) {

            return;
        }

        $this->getProvider($entity->getProviderName())->prePersist($entity);

    }
}

