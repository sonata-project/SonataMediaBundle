<?php

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
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
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
            $container->getDefinition($id)->addMethodCall('addFormat', array('admin', $format));
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param ContainerBuilder $container
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

    /**
     * @param ContainerBuilder $container
     */
    public function attachProviders(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('sonata.media.pool');
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $settings
     */
    public function attachArguments(ContainerBuilder $container, array $settings)
    {
        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {
            foreach ($settings['providers'] as $name => $config) {
                if ($config['service'] == $id) {
                    $definition = $container->getDefinition($id);

                    $definition
                        ->replaceArgument(1, new Reference($config['filesystem']))
                        ->replaceArgument(2, new Reference($config['cdn']))
                        ->replaceArgument(3, new Reference($config['generator']))
                        ->replaceArgument(4, new Reference($config['thumbnail']))
                    ;

                    if ($config['resizer']) {
                        $definition->addMethodCall('setResizer', array(new Reference($config['resizer'])));
                    }
                }
            }
        }
    }

    /**
     * Define the default settings to the config array.
     *
     * @param ContainerBuilder $container
     * @param array            $settings
     */
    public function applyFormats(ContainerBuilder $container, array $settings)
    {
        foreach ($settings['contexts'] as $name => $context) {
            // add the different related formats
            foreach ($context['providers'] as $id) {
                $definition = $container->getDefinition($id);

                foreach ($context['formats'] as $format => $config) {
                    $config['quality'] = isset($config['quality']) ? $config['quality'] : 80;
                    $config['format'] = isset($config['format']) ? $config['format'] : 'jpg';
                    $config['height'] = isset($config['height']) ? $config['height'] : false;
                    $config['constraint'] = isset($config['constraint']) ? $config['constraint'] : true;

                    $formatName = sprintf('%s_%s', $name, $format);
                    $definition->addMethodCall('addFormat', array($formatName, $config));
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getExtensionConfig(ContainerBuilder $container)
    {
        $config = $container->getExtensionConfig('sonata_media');
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), $config);
    }
}
