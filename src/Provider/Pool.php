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

use Sonata\Form\Validator\ErrorElement;
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
    private $providers = [];

    /**
     * @var array
     */
    private $contexts = [];

    /**
     * @var DownloadStrategyInterface[]
     */
    private $downloadStrategies = [];

    /**
     * @var string
     */
    private $defaultContext;

    public function __construct(string $context)
    {
        $this->defaultContext = $context;
    }

    /**
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function getProvider(?string $name): MediaProviderInterface
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

    public function addProvider(string $name, MediaProviderInterface $instance): void
    {
        $this->providers[$name] = $instance;
    }

    public function addDownloadStrategy(string $name, DownloadStrategyInterface $security): void
    {
        $this->downloadStrategies[$name] = $security;
    }

    public function setProviders(array $providers): void
    {
        $this->providers = $providers;
    }

    /**
     * @return MediaProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function addContext(string $name, array $providers = [], array $formats = [], array $download = []): void
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

    public function hasContext(string $name): bool
    {
        return isset($this->contexts[$name]);
    }

    public function getContext(string $name): ?array
    {
        if (!$this->hasContext($name)) {
            return null;
        }

        return $this->contexts[$name];
    }

    /**
     * Returns the context list.
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }

    public function getProviderNamesByContext(string $name): ?array
    {
        $context = $this->getContext($name);

        if (!$context) {
            return null;
        }

        return $context['providers'];
    }

    public function getFormatNamesByContext(string $name): ?array
    {
        $context = $this->getContext($name);

        if (!$context) {
            return null;
        }

        return $context['formats'];
    }

    public function getProvidersByContext(string $name): array
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

    public function getProviderList(): array
    {
        $choices = [];
        foreach (array_keys($this->providers) as $name) {
            $choices[$name] = $name;
        }

        return $choices;
    }

    /**
     * @throws \RuntimeException
     */
    public function getDownloadStrategy(MediaInterface $media): DownloadStrategyInterface
    {
        $context = $this->getContext($media->getContext());

        $id = $context['download']['strategy'];

        if (!isset($this->downloadStrategies[$id])) {
            throw new \RuntimeException('Unable to retrieve the download security : '.$id);
        }

        return $this->downloadStrategies[$id];
    }

    public function getDownloadMode(MediaInterface $media): ?string
    {
        $context = $this->getContext($media->getContext());

        return $context['download']['mode'];
    }

    public function getDefaultContext(): string
    {
        return $this->defaultContext;
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {
        if (!$media->getProviderName()) {
            return;
        }

        $provider = $this->getProvider($media->getProviderName());

        $provider->validate($errorElement, $media);
    }
}
