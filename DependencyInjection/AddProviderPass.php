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

        // not very clean but don't know how to do that for now
        $settings = false;
        $post = false;
        $methods  = $pool->getMethodCalls();
        foreach ($methods as $pos => $calls) {
            if ($calls[0] == '__hack__') {
                $settings = $calls[1];
                break;
            }
        }

        if ($settings) {
            unset($methods[$pos]);
        }

        $pool->setMethodCalls($methods);

        foreach ($container->findTaggedServiceIds('sonata.media.provider') as $id => $attributes) {

            $definition = $container->getDefinition($id);

            if ($settings) {
                $this->applySettings($id, $definition, $settings);
            }

            $pool->addMethodCall('addProvider', array($id, new Reference($id)));
        }
    }

    /**
     * Define the default settings to the config array
     *
     * @param string $id
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array $settings
     * @return void
     */
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
    }
}
