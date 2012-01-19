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

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;

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
        // todo: update the code to use the Configuration class
        $config = call_user_func_array('array_merge_recursive', $config);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('provider.xml');
        $loader->load('media.xml');
        $loader->load('twig.xml');
        $loader->load('block.xml');
        $loader->load('security.xml');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['SonataFormatterBundle'])) {
            $loader->load('formatter.xml');
        }

        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('sonata.media.thumbnail.liip_imagine');
        }

        if (!in_array(strtolower($config['db_driver']), array('doctrine_orm', 'doctrine_mongodb'))) {
            throw new \InvalidArgumentException(sprintf('Invalid db driver "%s".', $config['db_driver']));
        }

        $config['default_context'] = isset($config['default_context']) ? $config['default_context'] : 'default';

        $loader->load(sprintf('%s.xml', $config['db_driver']));
        $loader->load(sprintf('%s_admin.xml', $config['db_driver']));

        $this->configureFilesystemAdapter($container, $config);
        $this->configureCdnAdapter($container, $config);

        $pool = $container->getDefinition('sonata.media.pool');
        $pool->replaceArgument(0, $config['default_context']);

        // this shameless hack is done in order to have one clean configuration
        // for adding formats ....
        $pool->addMethodCall('__hack__', $config);

        $strategies = array();

        foreach ($config['contexts'] as $name => $settings) {
            $formats = array();

            foreach ($settings['formats'] as $format => $value) {
                $formats[$name.'_'.$format] = $value;
            }

            if (!isset($settings['download'])) {
                $settings['download'] = array();
            }

            if (!isset($settings['download']['mode'])) {
                $settings['download']['mode'] = 'http';
            }

            if (!isset($settings['download']['strategy'])) {
                $settings['download']['strategy'] = 'sonata.media.security.superadmin_strategy';
            }

            $strategies[] = $settings['download']['strategy'];
            $pool->addMethodCall('addContext', array($name, $settings['providers'], $formats, $settings['download']));
        }

        $strategies = array_unique($strategies);

        foreach ($strategies as $strategyId) {
            $pool->addMethodCall('addDownloadSecurity', array($strategyId, new Reference($strategyId)));
        }

        if ('doctrine_orm' == $config['db_driver']) {
            $this->registerDoctrineMapping($config);
        }
    }

    /**
     * @param array $config
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation('Application\\Sonata\\MediaBundle\\Entity\\Media', 'mapOneToMany', array(
            'fieldName'     => 'galleryHasMedias',
            'targetEntity'  => 'Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia',
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => 'media',
            'orphanRemoval' => false,
        ));

        $collector->addAssociation('Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia', 'mapOneToOne', array(
            'fieldName'     => 'gallery',
            'targetEntity'  => 'Application\\Sonata\\MediaBundle\\Entity\\Gallery',
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => 'galleryHasMedias',
            'joinColumns'   =>  array(
                array(
                    'name'  => 'gallery_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation('Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia', 'mapOneToOne', array(
            'fieldName'     => 'media',
            'targetEntity'  => 'Application\\Sonata\\MediaBundle\\Entity\\Media',
            'cascade'       => array(
                 'persist',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => 'galleryHasMedias',
            'joinColumns'   => array(
                array(
                    'name'  => 'media_id',
                    'referencedColumnName' => 'id',
                ),
            ),
            'orphanRemoval' => false,
        ));

        $collector->addAssociation('Application\\Sonata\\MediaBundle\\Entity\\Gallery', 'mapOneToMany', array(
            'fieldName'     => 'galleryHasMedias',
            'targetEntity'  => 'Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia',
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => 'gallery',
            'orphanRemoval' => false,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));
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
        if ($container->hasDefinition('sonata.media.cdn.server') && isset($config['cdn']['sonata.media.cdn.server'])) {
            $definition     = $container->getDefinition('sonata.media.cdn.server');
            $configuration  = $config['cdn']['sonata.media.cdn.server'];
            $definition->replaceArgument(0, $configuration['path']);
        }

        if ($container->hasDefinition('sonata.media.cdn.panther') && isset($config['cdn']['sonata.media.cdn.panther'])) {
            $definition     = $container->getDefinition('sonata.media.cdn.panther');
            $configuration  = $config['cdn']['sonata.media.cdn.panther'];
            $definition->replaceArgument(0, $configuration['path']);
            $definition->replaceArgument(1, $configuration['username']);
            $definition->replaceArgument(2, $configuration['password']);
            $definition->replaceArgument(3, $configuration['site_id']);
        }

        if ($container->hasDefinition('sonata.media.cdn.fallback') && isset($config['cdn']['sonata.media.cdn.fallback'])) {
            $definition     = $container->getDefinition('sonata.media.cdn.fallback');
            $configuration  = $config['cdn']['sonata.media.cdn.fallback'];
            $definition->replaceArgument(0, new Reference($configuration['cdn']));
            $definition->replaceArgument(1, new Reference($configuration['fallback']));
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
        if ($container->hasDefinition('sonata.media.adapter.filesystem.local') && isset($config['filesystem']['sonata.media.adapter.filesystem.local'])) {
            $definition = $container->getDefinition('sonata.media.adapter.filesystem.local');
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.local'];
            $definition->addArgument($configuration['directory']);
            $definition->addArgument($configuration['create']);
        }

        // add the default configuration for the FTP filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.ftp') && isset($config['filesystem']['sonata.media.adapter.filesystem.ftp'])) {
            $definition = $container->getDefinition('sonata.media.adapter.filesystem.ftp');
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.ftp'];
            $definition->addArgument($configuration['directory']);
            $definition->addArgument($configuration['username']);
            $definition->addArgument($configuration['password']);
            $definition->addArgument($configuration['port']);
            $definition->addArgument($configuration['passive']);
            $definition->addArgument($configuration['create']);
        }

        // add the default configuration for the S3 filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.s3') && isset($config['filesystem']['sonata.media.adapter.filesystem.s3'])) {
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.s3'];

            $definition = $container->getDefinition('sonata.media.adapter.filesystem.s3');
            $definition->replaceArgument(0, new Reference('sonata.media.adapter.service.s3'));
            $definition->replaceArgument(1, $configuration['bucket']);
            $definition->replaceArgument(2, $configuration['create']);

            $definition = $container->getDefinition('sonata.media.adapter.service.s3');
            $definition->replaceArgument(0, array(
                'secret' => $configuration['secretKey'],
                'key'    => $configuration['accessKey'],
            ));
        }

        if ($container->hasDefinition('sonata.media.adapter.filesystem.replicate') && isset($config['filesystem']['sonata.media.adapter.filesystem.replicate'])) {
            $definition = $container->getDefinition('sonata.media.adapter.filesystem.replicate');
            $configuration =  $config['filesystem']['sonata.media.adapter.filesystem.replicate'];
            $definition->replaceArgument(0, new Reference($configuration['master']));
            $definition->replaceArgument(1, new Reference($configuration['slave']));
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