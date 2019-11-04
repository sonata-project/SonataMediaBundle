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
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
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

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return array
     */
    public function fixSettings(ContainerBuilder $container)
    {
        @trigger_error(
            'The '.__METHOD__.' method is deprecated since 3.5, to be removed in 4.0.',
            E_USER_DEPRECATED
        );

        return $this->getExtensionConfig($container);
    }

    public function attachProviders(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('sonata.media.pool');
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', [$id, new Reference($id)]);
        }
    }

    public function attachArguments(ContainerBuilder $container, array $settings)
    {
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            foreach ($settings['providers'] as $name => $config) {
                if ($config['service'] === $id) {
                    $definition = $container->getDefinition($id);

                    $definition
                        ->replaceArgument(1, new Reference($config['filesystem']))
                        ->replaceArgument(2, new Reference($config['cdn']))
                        ->replaceArgument(3, new Reference($config['generator']))
                        ->replaceArgument(4, new Reference($config['thumbnail']))
                    ;

                    if ($config['resizer']) {
                        $definition->addMethodCall('setResizer', [new Reference($config['resizer'])]);
                    }
                }
            }
        }
    }

    /**
     * Define the default settings to the config array.
     */
    public function applyFormats(ContainerBuilder $container, array $settings)
    {
        foreach ($settings['contexts'] as $name => $context) {
            // add the different related formats
            foreach ($context['providers'] as $id) {
                $definition = $container->getDefinition($id);

                foreach ($context['formats'] as $format => $config) {
                    $config['quality'] = $config['quality'] ?? 80;
                    $config['format'] = $config['format'] ?? 'jpg';
                    $config['height'] = $config['height'] ?? null;
                    $config['constraint'] = $config['constraint'] ?? true;
                    $config['resizer'] = $config['resizer'] ?? false;

                    $formatName = sprintf('%s_%s', $name, $format);
                    $definition->addMethodCall('addFormat', [$formatName, $config]);
                }
            }
        }
    }

    private function getExtensionConfig(ContainerBuilder $container): array
    {
        $config = $container->getExtensionConfig('sonata_media');
        $config = $container->getParameterBag()->resolveValue($config);
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $config);
    }
}
