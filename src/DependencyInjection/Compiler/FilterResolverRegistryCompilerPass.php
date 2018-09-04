<?php

namespace SonataMediaBundle\DependencyInjection\Compiler;

use Liip\ImagineBundle\DependencyInjection\Compiler\AbstractCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilterResolverRegistryCompilerPass extends AbstractCompilerPass
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.cache.resolver');

        if (count($tags) > 0 && $container->hasDefinition('sonata.media.liip_imagine.filter_resolver_registry')) {
            $manager = $container->getDefinition('sonata.media.liip_imagine.filter_resolver_registry');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', [$tag[0]['resolver'], new Reference($id)]);
                $this->log($container, 'Registered cache resolver: %s', $id);
            }
        }
    }
}
