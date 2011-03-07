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

    /**
     * @var array
     */
    protected $settings = array();


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


    public function postUpdate(Media $media)
    {

        $this->getProvider($media->getProviderName())->postUpdate($media);
    }

    public function postRemove(Media $media)
    {

        $this->getProvider($media->getProviderName())->postRemove($media);
    }

    public function postPersist(Media $media)
    {

        $this->getProvider($media->getProviderName())->postPersist($media);
    }

    public function preUpdate(Media $media)
    {

        $this->getProvider($media->getProviderName())->preUpdate($media);
    }

    public function preRemove(Media $media)
    {

        $this->getProvider($media->getProviderName())->preRemove($media);
    }

    public function prePersist(Media $media)
    {
        $this->getProvider($media->getProviderName())->prePersist($media);
    }

    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getProviderList()
    {
        $choices = array();
        foreach (array_keys($this->providers) as $name) {
            $choices[$name] = $name;
        }

        return $choices;
    }
}

