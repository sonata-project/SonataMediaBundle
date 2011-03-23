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

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Finder\Finder;

/**
 * MediaExtension
 *
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataMediaExtension extends Extension
{

    /**
     * Loads the url shortener configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.xml');
        $loader->load('provider.xml');
        $loader->load('media.xml');

        $config = call_user_func_array('array_merge_recursive', $config);

        $this->configureFilesystemAdapter($container, $config);
        $this->configureCdnAdapter($container, $config);

        $pool = $container->getDefinition('sonata.media.pool');

        // this shameless hack is done in order to have one clean configuration
        // for adding formats ....
        $pool->addMethodCall('__hack__', $config);

        foreach ($config['contexts'] as $name => $settings) {
            $formats = array();

            foreach($settings['formats'] as $format => $value) {
                $formats[$name.'_'.$format] = $value;
            }

            $pool->addMethodCall('addContext', array($name, $settings['providers'], $formats));
        }

        // register template helper
        $definition = new Definition(
            'Sonata\MediaBundle\Templating\Helper\MediaHelper',
            array(
                 new Reference('sonata.media.pool'),
                 new Reference('templating')
            )
        );
        $definition->addTag('templating.helper', array('alias' => 'media'));
        $definition->addTag('templating.helper', array('alias' => 'thumbnail'));

        $container->setDefinition('templating.helper.media', $definition);

        // register the twig extension
        $container
            ->register('twig.extension.media', 'Sonata\MediaBundle\Twig\Extension\MediaExtension')
            ->addTag('twig.extension');

    }

    /**
     * Inject CDN dependency to default provider
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param  $config
     * @return void
     */
    public function configureCdnAdapter(ContainerBuilder $container, $config)
    {
        // add the default configuration for the server cdn
        if($container->hasDefinition('sonata.media.cdn.server') && isset($config['cdn']['sonata.media.cdn.server'])) {
            $definition     = $container->getDefinition('sonata.media.cdn.server');
            $configuration  = $config['cdn']['sonata.media.cdn.server'];
            $definition->setArgument(0, $configuration['path']);
        }

        if($container->hasDefinition('sonata.media.cdn.panther') && isset($config['cdn']['sonata.media.cdn.panther'])) {
            $definition     = $container->getDefinition('sonata.media.cdn.panther');
            $configuration  = $config['cdn']['sonata.media.cdn.panther'];
            $definition->setArgument(0, $configuration['path']);
            $definition->setArgument(1, $configuration['username']);
            $definition->setArgument(2, $configuration['password']);
            $definition->setArgument(3, $configuration['site_id']);
        }
    }
    /**
     * Inject filesystem dependency to default provider
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param  $config
     * @return void
     */
    public function configureFilesystemAdapter(ContainerBuilder $container, $config)
    {

        // add the default configuration for the local filesystem
        if($container->hasDefinition('sonata.media.adapter.filesystem.local') && isset($config['filesystem']['sonata.media.adapter.filesystem.local'])) {
            $definition = $container->getDefinition('sonata.media.adapter.filesystem.local');
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.local'];
            $definition->addArgument($configuration['directory']);
            $definition->addArgument($configuration['create']);
        }

        // add the default configuration for the FTP filesystem
        if($container->hasDefinition('sonata.media.adapter.filesystem.ftp') && isset($config['filesystem']['sonata.media.adapter.filesystem.ftp'])) {
            $definition = $container->getDefinition('sonata.media.adapter.filesystem.ftp');
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.ftp'];
            $definition->addArgument($configuration['directory']);
            $definition->addArgument($configuration['username']);
            $definition->addArgument($configuration['password']);
            $definition->addArgument($configuration['port']);
            $definition->addArgument($configuration['passive']);
            $definition->addArgument($configuration['create']);
        }
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {

        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {

        return 'http://www.sonata-project.org/schema/dic/media';
    }

    public function getAlias()
    {

        return "sonata_media";
    }
}