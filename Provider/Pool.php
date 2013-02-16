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
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Security\DownloadStrategyInterface;
use Sonata\AdminBundle\Validator\ErrorElement;

class Pool
{
    /**
     * @var array
     */
    protected $providers = array();

    protected $contexts = array();

    protected $downloadSecurities = array();

    protected $defaultContext;

    /**
     * @param string $context
     */
    public function __construct($context)
    {
        $this->defaultContext = $context;
    }

    /**
     * @throws \RuntimeException
     *
     * @param string $name
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    public function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \RuntimeException(sprintf('unable to retrieve the provider named : `%s`', $name));
        }

        return $this->providers[$name];
    }

    /**
     * @param string                 $name
     * @param MediaProviderInterface $instance
     *
     * @return void
     */
    public function addProvider($name, MediaProviderInterface $instance)
    {
        $this->providers[$name] = $instance;
    }

    /**
     * @param string                                                 $name
     * @param \Sonata\MediaBundle\Security\DownloadStrategyInterface $security
     */
    public function addDownloadSecurity($name, DownloadStrategyInterface $security)
    {
        $this->downloadSecurities[$name] = $security;
    }

    /**
     * @param array $providers
     *
     * @return void
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param string $name
     * @param array  $providers
     * @param array  $formats
     * @param array  $download
     *
     * @return void
     */
    public function addContext($name, array $providers = array(), array $formats = array(), array $download = array())
    {
        if (!$this->hasContext($name)) {
            $this->contexts[$name] = array(
                'providers' => array(),
                'formats'   => array(),
                'download'  => array(),
            );
        }

        $this->contexts[$name]['providers'] = $providers;
        $this->contexts[$name]['formats']   = $formats;
        $this->contexts[$name]['download']  = $download;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasContext($name)
    {
        return isset($this->contexts[$name]);
    }

    /**
     * @param string $name
     *
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
     * Returns the context list
     *
     * @return array
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @param string $name
     *
     * @return array
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
     * @param string $name
     *
     * @return array
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
     *
     * @return array
     */
    public function getProvidersByContext($name)
    {
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

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return \Sonata\MediaBundle\Security\DownloadStrategyInterface
     * @throws \RuntimeException
     */
    public function getDownloadSecurity(MediaInterface $media)
    {
        $context = $this->getContext($media->getContext());

        $id = $context['download']['strategy'];

        if (!isset($this->downloadSecurities[$id])) {
            throw new \RuntimeException('Unable to retrieve the download security : ' . $id);
        }

        return $this->downloadSecurities[$id];
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return string
     */
    public function getDownloadMode(MediaInterface $media)
    {
        $context = $this->getContext($media->getContext());

        return $context['download']['mode'];
    }

    /**
     * @return string
     */
    public function getDefaultContext()
    {
        return $this->defaultContext;
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\MediaBundle\Model\MediaInterface   $media
     *
     * @return void
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
        $provider = $this->getProvider($media->getProviderName());

        $provider->validate($errorElement, $media);
    }
}
