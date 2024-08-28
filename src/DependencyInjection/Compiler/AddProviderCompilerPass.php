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

namespace Sonata\MediaBundle\DependencyInjection\Compiler;

use Sonata\MediaBundle\DependencyInjection\Configuration;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @internal
 */
final class AddProviderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = $this->getExtensionConfig($container);

        // define configuration per provider
        $this->applyFormats($container, $config);
        $this->attachArguments($container, $config);
        $this->attachProviders($container);

        $format = $container->getParameter('sonata.media.admin_format');

        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $container->getDefinition($id)->addMethodCall(
                'addFormat',
                [MediaProviderInterface::FORMAT_ADMIN, $format]
            );
        }
    }

    private function attachProviders(ContainerBuilder $container): void
    {
        $pool = $container->getDefinition('sonata.media.pool');
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', [$id, new Reference($id)]);
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function attachArguments(ContainerBuilder $container, array $config): void
    {
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            foreach ($config['providers'] as $provider) {
                if ($provider['service'] === $id) {
                    $definition = $container->getDefinition($id);

                    $definition
                        ->replaceArgument(1, new Reference($provider['filesystem']))
                        ->replaceArgument(2, new Reference($provider['cdn']))
                        ->replaceArgument(3, new Reference($provider['generator']))
                        ->replaceArgument(4, new Reference($provider['thumbnail']));

                    if (null !== $provider['resizer']) {
                        $definition->addMethodCall('setResizer', [new Reference($provider['resizer'])]);
                    }
                }
            }
        }
    }

    /**
     * Define the default settings to the config array.
     *
     * @param array<string, mixed> $config
     */
    private function applyFormats(ContainerBuilder $container, array $config): void
    {
        foreach ($config['contexts'] as $name => $context) {
            // add the different related formats
            foreach ($context['providers'] as $id) {
                $definition = $container->getDefinition($id);

                foreach ($context['formats'] as $format => $formatConfig) {
                    $definition->addMethodCall('addFormat', [\sprintf('%s_%s', $name, $format), $formatConfig]);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function getExtensionConfig(ContainerBuilder $container): array
    {
        $config = $container->getExtensionConfig('sonata_media');
        $config = $container->getParameterBag()->resolveValue($config);
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $config);
    }
}
