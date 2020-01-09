<?php

declare(strict_types=1);

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

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Pool
{
    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @var array
     */
    protected $contexts = [];

    /**
     * NEXT_MAJOR: remove this property.
     *
     * @deprecated since sonata-project/media-bundle 3.1 and will be removed in 4.0. Use $downloadStrategies instead
     *
     * @var DownloadStrategyInterface[]
     */
    protected $downloadSecurities = [];

    /**
     * @var DownloadStrategyInterface[]
     */
    protected $downloadStrategies = [];

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
     * @param string $name
     *
     * @throws \RuntimeException
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
     * @param string $name
     */
    public function addProvider($name, MediaProviderInterface $instance)
    {
        $this->providers[$name] = $instance;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.1, to be removed in 4.0
     *
     * @param string $name
     */
    public function addDownloadSecurity($name, DownloadStrategyInterface $security)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since version 3.1 and will be removed in 4.0.',
            E_USER_DEPRECATED
        );

        $this->downloadSecurities[$name] = $security;

        $this->addDownloadStrategy($name, $security);
    }

    /**
     * @param string $name
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
     */
    public function addContext($name, array $providers = [], array $formats = [], array $download = [])
    {
        if (!$this->hasContext($name)) {
            $this->contexts[$name] = [
                'providers' => [],
                'formats' => [],
                'download' => [],
            ];
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
            return null;
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
     * @param string $name
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
     *
     * @return array
     */
    public function getProvidersByContext($name)
    {
        $providers = [];

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
        $choices = [];
        foreach (array_keys($this->providers) as $name) {
            $choices[$name] = $name;
        }

        return $choices;
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.1, to be removed in 4.0
     *
     * @throws \RuntimeException
     *
     * @return DownloadStrategyInterface
     */
    public function getDownloadSecurity(MediaInterface $media)
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 3.1 and will be removed in 4.0.', E_USER_DEPRECATED);

        return $this->getDownloadStrategy($media);
    }

    /**
     * @throws \RuntimeException
     *
     * @return DownloadStrategyInterface
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

    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
        if (!$media->getProviderName()) {
            return;
        }

        $provider = $this->getProvider($media->getProviderName());

        $provider->validate($errorElement, $media);
    }
}
