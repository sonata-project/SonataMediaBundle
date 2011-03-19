<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\MediaBundle\Provider;

use Sonata\MediaBundle\Entity\BaseMedia as Media;

/**
 *
 * It is not possible to use doctrine as the update event cannot update non updated fields ...
 *
 * @throws RuntimeException
 *
 */
class Pool
{

    /**
     * @var array
     */
    protected $providers = array();

    public function getProvider($name)
    {

        if (!isset($this->providers[$name])) {
            throw new \RuntimeException(sprintf('unable to retrieve the provider named : %s', $name));
        }

        return $this->providers[$name];
    }

    public function addProvider($name, $instance)
    {
        $this->providers[$name] = $instance;
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function postUpdate(Media $media)
    {
        $this->getProvider($media->getProviderName())->postUpdate($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function postRemove(Media $media)
    {
        $this->getProvider($media->getProviderName())->postRemove($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function postPersist(Media $media)
    {
        $this->getProvider($media->getProviderName())->postPersist($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function preUpdate(Media $media)
    {
        $this->getProvider($media->getProviderName())->preUpdate($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function preRemove(Media $media)
    {
        $this->getProvider($media->getProviderName())->preRemove($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function prePersist(Media $media)
    {
        $this->getProvider($media->getProviderName())->prePersist($media);
    }

    /**
     * @param  $providers
     * @return void
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @return array
     */
    public function getProviderList()
    {
        $choices = array();
        foreach (array_keys($this->providers) as $name) {
            $choices[$name] = $name;
        }

        return $choices;
    }
}

