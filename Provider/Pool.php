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

use Sonata\MediaBundle\Model\MediaInterface;

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

    protected $contexts = array();

    /**
     * @throws \RuntimeException
     * @param  $name
     * @return \Sonata\MediaBundle\Provider\ProviderInterface
     */
    public function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \RuntimeException(sprintf('unable to retrieve the provider named : `%s`', $name));
        }

        return $this->providers[$name];
    }

    public function addProvider($name, $instance)
    {
        $this->providers[$name] = $instance;
    }

    public function getProviderByProduct(ProductInterface $product)
    {
        foreach($this->providers as $provider) {
            if (get_class($product) == $provider->getClass()) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function postUpdate(MediaInterface $media)
    {
        $this->getProvider($media->getProviderName())->postUpdate($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function postRemove(MediaInterface $media)
    {
        $this->getProvider($media->getProviderName())->postRemove($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function postPersist(MediaInterface $media)
    {
        $this->getProvider($media->getProviderName())->postPersist($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preUpdate(MediaInterface $media)
    {
        $this->getProvider($media->getProviderName())->preUpdate($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preRemove(MediaInterface $media)
    {
        $this->getProvider($media->getProviderName())->preRemove($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function prePersist(MediaInterface $media)
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
     * @param string $name
     * @param array $providers
     * @param array $formats
     * @return void
     */
    public function addContext($name, array $providers = array(), array $formats = array())
    {
        if (!$this->hasContext($name)) {
            $this->contexts[$name] = array(
                'providers' => array(),
                'formats'   => array(),
            );
        }

        $this->contexts[$name]['providers']    = $providers;
        $this->contexts[$name]['formats']      = $formats;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasContext($name)
    {
        return isset($this->contexts[$name]);
    }

    /**
     * @param  $name
     * @return array|null
     */
    public function getContext($name)
    {
        if (!$this->hasContext($name)) {
            return null;
        }


        return $this->contexts[$name];
    }

    /**
     *
     * @return array|null
     */
    public function getProviderNamesByContext($name)
    {
        $context = $this->getContext($name);

        if (!$context) {
            return null;
        }

        return $context['providers'];
    }

    /**
     *
     * @return array|null
     */
    public function getFormatNamesByContext($name)
    {
        $context = $this->getContext($name);

        if (!$context) {
            return null;
        }

        return $context['formats'];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getProvidersByContext($name) {
        $providers = array();

        if (!$this->hasContext($name)) {
            return $providers;
        }

        foreach ($this->getProviderNamesByContext($name) as $name) {
            $providers[] = $this->getProvider($name);
        }

        return $providers;
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

