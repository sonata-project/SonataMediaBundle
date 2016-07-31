<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Security\DownloadStrategyInterface;

class Pool
{
    /**
     * @var array
     */
    protected $providers = array();

    /**
     * @var array
     */
    protected $contexts = array();

    /**
     * @deprecated Deprecated since version 3.1 and will be removed in 4.0. Use $downloadStrategies instead
     *
     * @var DownloadStrategyInterface[]
     */
    protected $downloadSecurities = array();

    /**
     * @var DownloadStrategyInterface[]
     */
    protected $downloadStrategies = array();

    /**
     * @var string
     */
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
     * @return MediaProviderInterface
     */
    public function getProvider($name)
    {
        if (!$name) {
            throw new \InvalidArgumentException('Provider name cannot be empty, did you forget to call setProviderName() in your Media object?');
        }
        if (empty($this->providers)) {
            throw new \RuntimeException(sprintf('Unable to retrieve provider named "%s" since there are no providers configured yet.', $name));
        }
        if (!isset($this->providers[$name])) {
            throw new \InvalidArgumentException(sprintf('Unable to retrieve the provider named "%s". Available providers are %s.', $name, '"'.implode('", "', $this->getProviderList()).'"'));
        }

        return $this->providers[$name];
    }

    /**
     * @param string                 $name
     * @param MediaProviderInterface $instance
     */
    public function addProvider($name, MediaProviderInterface $instance)
    {
        $this->providers[$name] = $instance;
    }

    /**
     * @deprecated Deprecated since version 3.1, to be removed in 4.0
     *
     * @param string                    $name
     * @param DownloadStrategyInterface $security
     */
    public function addDownloadSecurity($name, DownloadStrategyInterface $security)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        $this->downloadSecurities[$name] = $security;

        $this->addDownloadStrategy($name, $security);
    }

    /**
     * @param string                    $name
     * @param DownloadStrategyInterface $security
     */
    public function addDownloadStrategy($name, DownloadStrategyInterface $security)
    {
        $this->downloadStrategies[$name] = $security;
    }

    /**
     * @param array $providers
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return MediaProviderInterface[]
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
     */
    public function addContext($name, array $providers = array(), array $formats = array(), array $download = array())
    {
        if (!$this->hasContext($name)) {
            $this->contexts[$name] = array(
                'providers' => array(),
                'formats' => array(),
                'download' => array(),
            );
        }

        $this->contexts[$name]['providers'] = $providers;
        $this->contexts[$name]['formats'] = $formats;
        $this->contexts[$name]['download'] = $download;
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
            return;
        }

        return $this->contexts[$name];
    }

    /**
     * Returns the context list.
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
            return;
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
            return;
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
     * @deprecated Deprecated since version 3.1, to be removed in 4.0
     *
     * @param MediaInterface $media
     *
     * @return DownloadStrategyInterface
     *
     * @throws \RuntimeException
     */
    public function getDownloadSecurity(MediaInterface $media)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->getDownloadStrategy($media);
    }

    /**
     * @param MediaInterface $media
     *
     * @return DownloadStrategyInterface
     *
     * @throws \RuntimeException
     */
    public function getDownloadStrategy(MediaInterface $media)
    {
        $context = $this->getContext($media->getContext());

        $id = $context['download']['strategy'];

        // NEXT_MAJOR: remove this line with the next major release.
        if (isset($this->downloadSecurities[$id])) {
            return $this->downloadSecurities[$id];
        }

        if (!isset($this->downloadStrategies[$id])) {
            throw new \RuntimeException('Unable to retrieve the download security : '.$id);
        }

        return $this->downloadStrategies[$id];
    }

    /**
     * @param MediaInterface $media
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
     * @param ErrorElement   $errorElement
     * @param MediaInterface $media
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
        if (!$media->getProviderName()) {
            return;
        }

        $provider = $this->getProvider($media->getProviderName());

        $provider->validate($errorElement, $media);
    }
}
