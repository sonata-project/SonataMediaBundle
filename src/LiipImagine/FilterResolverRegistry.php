<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\LiipImagine;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class FilterResolverRegistry implements ResolverRegistryInterface
{
    /**
     * @var ResolverInterface[]
     */
    private $resolvers;
    /**
     * @var FilterConfiguration
     */
    private $filterConfiguration;
    /**
     * @var string
     */
    private $defaultResolver;

    /**
     * @param FilterConfiguration $filterConfiguration
     * @param string              $defaultResolver
     */
    public function __construct(FilterConfiguration $filterConfiguration, $defaultResolver = 'default')
    {
        $this->filterConfiguration = $filterConfiguration;
        $this->defaultResolver = $defaultResolver;
    }

    public function addResolver($name, ResolverInterface $resolver)
    {
        if (isset($this->resolvers[$name])) {
            throw new \LogicException(sprintf(
                'There is already a resolver `%s` registered with name `%s`',
                \get_class($this->resolvers[$name]),
                $name
            ));
        }

        $this->resolvers[$name] = $resolver;
    }

    public function getResolver($name)
    {
        $config = $this->filterConfiguration->get($name);
        $name = empty($config['cache']) ? $this->defaultResolver : $config['cache'];
        if (!isset($this->resolvers[$name])) {
            throw new \OutOfBoundsException(sprintf(
                'No cache resolver `%s registered, verify your resolver tags configuration',
                $name
            ));
        }

        return $this->resolvers[$name];
    }
}
