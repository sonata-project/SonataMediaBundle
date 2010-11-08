<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Bundle\MediaBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;

use Symfony\Component\Finder\Finder;

/**
 * MediaExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class MediaExtension extends Extension {

    /**
     * Loads the url shortener configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function configLoad($config, ContainerBuilder $container) {

        $definition = new Definition($config['class']);

        foreach($config['providers'] as $name => $provider) {

            $provider_name = sprintf('media.provider.%s', $name);

            $config['settings']['quality']      = isset($config['settings']['quality']) ? $config['settings']['quality'] : 80;
            $config['settings']['format']       = isset($config['settings']['format'])  ? $config['settings']['format'] : 'jpg';
            $config['settings']['height']       = isset($config['settings']['height'])  ? $config['settings']['height'] : 'false';
            $config['settings']['constraint']   = isset($config['settings']['constraint'])  ? $config['settings']['constraint'] : true;

            $provider['formats']                = is_array($provider['formats']) ? $provider['formats']  : array();
            
            $provider_definition = new Definition($provider['class'], array(
                $name,
                new Reference($config['em']),
                $config['settings'],
            ));

            foreach($provider['formats'] as $format_name => $format_definition) {
                $provider_definition->addMethodCall('addFormat', array($format_name, $format_definition));
            }

            $container->setDefinition($provider_name, $provider_definition);

            $definition->addMethodCall('addProvider', array($name, new Reference($provider_name)));
        }

        $container->setDefinition('media.provider', $definition);
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath() {

        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace() {

        return 'http://www.sonata-project.org/schema/dic/media';
    }

    public function getAlias() {

        return "media";
    }
}