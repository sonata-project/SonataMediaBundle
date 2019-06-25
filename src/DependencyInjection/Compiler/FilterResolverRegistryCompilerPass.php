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

namespace SonataMediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class FilterResolverRegistryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('liip_imagine.cache.resolver');

        if (\count($tags) > 0 && $container->hasDefinition('sonata.media.liip_imagine.filter_resolver_registry')) {
            $manager = $container->getDefinition('sonata.media.liip_imagine.filter_resolver_registry');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', [$tag[0]['resolver'], new Reference($id)]);
            }
        }
    }
}
