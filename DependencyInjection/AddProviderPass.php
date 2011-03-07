<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * 
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class AddProviderPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {

        $pool = $container->getDefinition('sonata.media.pool');

        // TODO: no very clean but don't know how to do that for now
        $settings = false;
        foreach ($pool->getMethodCalls() as $calls) {
            if ($calls[0] == 'setSettings') {
                $settings = $calls[1];
            }
        }

        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {

            $definition = $container->getDefinition($id);

            if ($settings) {
                $this->applySettings($id, $definition, $settings);
            }
            
            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }

    }

    public function applySettings($id, Definition $definition, $settings)
    {

        // add the differents related formats
        if (isset($settings['providers'][$id]['formats'])) {
            $formats = $settings['providers'][$id]['formats'];

            foreach ($formats as $format => $config) {
                $config['quality']      = isset($config['quality']) ? $config['quality'] : 80;
                $config['format']       = isset($config['format'])  ? $config['format'] : 'jpg';
                $config['height']       = isset($config['height'])  ? $config['height'] : false;
                $config['constraint']   = isset($config['constraint'])  ? $config['constraint'] : true;

                $definition->addMethodCall('addFormat', array($format, $config));
            }
        }

        // compute the settings
        $settings = isset($settings['settings']) ? $settings['settings'] : array();
        if (isset($settings['providers'][$id]['settings'])) {
            $settings = array_merge($settings, $settings['providers'][$id]['settings']);
        }

        $definition->addMethodCall('setSettings', array($settings));
    }
}
