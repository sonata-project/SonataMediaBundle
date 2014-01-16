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
use Symfony\Component\Config\Definition\Processor;

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
     * @param array            $configs   An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor     = new Processor();
        $configuration = new Configuration();
        $config        = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('provider.xml');
        $loader->load('media.xml');
        $loader->load('twig.xml');
        $loader->load('block.xml');
        $loader->load('security.xml');
        $loader->load('extra.xml');
        $loader->load('form.xml');
        $loader->load('gaufrette.xml');
        $loader->load('validators.xml');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['SonataNotificationBundle'])) {
            $loader->load('consumer.xml');
        }

        if (isset($bundles['SonataFormatterBundle'])) {
            $loader->load('formatter.xml');
        }

        if (isset($bundles['SonataSeoBundle'])) {
            $loader->load('seo_block.xml');
        }

        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('sonata.media.thumbnail.liip_imagine');
        }

        if (!in_array(strtolower($config['db_driver']), array('doctrine_orm', 'doctrine_mongodb', 'doctrine_phpcr'))) {
            throw new \InvalidArgumentException(sprintf('SonataMediaBundle - Invalid db driver "%s".', $config['db_driver']));
        }

        if (!array_key_exists($config['default_context'], $config['contexts'])) {
            throw new \InvalidArgumentException(sprintf('SonataMediaBundle - Invalid default context : %s, available : %s', $config['default_context'], json_encode(array_keys($config['contexts']))));
        }

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

        $container->setParameter('sonata.media.resizer.simple.adapter.mode', $config['resizer']['simple']['mode']);
        $container->setParameter('sonata.media.resizer.square.adapter.mode', $config['resizer']['square']['mode']);

        $this->configureParameterClass($container, $config);
        $this->configureExtra($container, $config);
        $this->configureBuzz($container, $config);
        $this->configureProviders($container, $config);
        $this->configureClassesToCompile();
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureProviders(ContainerBuilder $container, $config)
    {
        $container->getDefinition('sonata.media.provider.image')
            ->replaceArgument(5, array_map('strtolower', $config['providers']['image']['allowed_extensions']))
            ->replaceArgument(6, $config['providers']['image']['allowed_mime_types'])
            ->replaceArgument(7, new Reference($config['providers']['image']['adapter']))
        ;

        $container->getDefinition('sonata.media.provider.file')
            ->replaceArgument(5, $config['providers']['file']['allowed_extensions'])
            ->replaceArgument(6, $config['providers']['file']['allowed_mime_types'])
        ;

        $container->getDefinition('sonata.media.provider.youtube')->replaceArgument(7, $config['providers']['youtube']['html5']);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureBuzz(ContainerBuilder $container, array $config)
    {
        $container->getDefinition('sonata.media.buzz.browser')
            ->replaceArgument(0, new Reference($config['buzz']['connector']));

        foreach (array(
            'sonata.media.buzz.connector.curl',
            'sonata.media.buzz.connector.file_get_contents'
        ) as $connector) {
            $container->getDefinition($connector)
                ->addMethodCall('setIgnoreErrors', array($config['buzz']['client']['ignore_errors']))
                ->addMethodCall('setMaxRedirects', array($config['buzz']['client']['max_redirects']))
                ->addMethodCall('setTimeout',      array($config['buzz']['client']['timeout']))
                ->addMethodCall('setVerifyPeer',   array($config['buzz']['client']['verify_peer']))
                ->addMethodCall('setProxy',        array($config['buzz']['client']['proxy']));
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureParameterClass(ContainerBuilder $container, array $config)
    {
        $container->setParameter('sonata.media.admin.media.entity', $config['class']['media']);
        $container->setParameter('sonata.media.admin.gallery.entity', $config['class']['gallery']);
        $container->setParameter('sonata.media.admin.gallery_has_media.entity', $config['class']['gallery_has_media']);

        $container->setParameter('sonata.media.media.class', $config['class']['media']);
        $container->setParameter('sonata.media.gallery.class', $config['class']['gallery']);

        $container->getDefinition('sonata.media.form.type.media')->replaceArgument(1, $config['class']['media']);
    }

    /**
     * @param array $config
     *
     * @return void
     */
    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['media'], 'mapOneToMany', array(
            'fieldName'     => 'galleryHasMedias',
            'targetEntity'  => $config['class']['gallery_has_media'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => 'media',
            'orphanRemoval' => false,
        ));

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', array(
            'fieldName'     => 'gallery',
            'targetEntity'  => $config['class']['gallery'],
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

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', array(
            'fieldName'     => 'media',
            'targetEntity'  => $config['class']['media'],
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

        $collector->addAssociation($config['class']['gallery'], 'mapOneToMany', array(
            'fieldName'     => 'galleryHasMedias',
            'targetEntity'  => $config['class']['gallery_has_media'],
            'cascade'       => array(
                'persist',
            ),
            'mappedBy'      => 'gallery',
            'orphanRemoval' => true,
            'orderBy'       => array(
                'position'  => 'ASC',
            ),
        ));
    }

    /**
     * Inject CDN dependency to default provider
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     *
     * @return void
     */
    public function configureCdnAdapter(ContainerBuilder $container, array $config)
    {
        // add the default configuration for the server cdn
        if ($container->hasDefinition('sonata.media.cdn.server') && isset($config['cdn']['server'])) {
            $container->getDefinition('sonata.media.cdn.server')
                ->replaceArgument(0, $config['cdn']['server']['path'])
            ;
        } else {
            $container->removeDefinition('sonata.media.cdn.server');
        }

        if ($container->hasDefinition('sonata.media.cdn.panther') && isset($config['cdn']['panther'])) {
            $container->getDefinition('sonata.media.cdn.panther')
                ->replaceArgument(0, $config['cdn']['panther']['path'])
                ->replaceArgument(1, $config['cdn']['panther']['username'])
                ->replaceArgument(2, $config['cdn']['panther']['password'])
                ->replaceArgument(3, $config['cdn']['panther']['site_id'])
            ;
        } else {
            $container->removeDefinition('sonata.media.cdn.panther');
        }

        if ($container->hasDefinition('sonata.media.cdn.fallback') && isset($config['cdn']['fallback'])) {
            $container->getDefinition('sonata.media.cdn.fallback')
                ->replaceArgument(0, new Reference($config['cdn']['fallback']['master']))
                ->replaceArgument(1, new Reference($config['cdn']['fallback']['fallback']))
            ;
        } else {
            $container->removeDefinition('sonata.media.cdn.fallback');
        }
    }

    /**
     * Inject filesystem dependency to default provider
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     *
     * @return void
     */
    public function configureFilesystemAdapter(ContainerBuilder $container, array $config)
    {
        // add the default configuration for the local filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.local') && isset($config['filesystem']['local'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.local')
                ->addArgument($config['filesystem']['local']['directory'])
                ->addArgument($config['filesystem']['local']['create'])
            ;
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.local');
        }

        // add the default configuration for the FTP filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.ftp') && isset($config['filesystem']['ftp'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.ftp')
                ->addArgument($config['filesystem']['ftp']['directory'])
                ->addArgument($config['filesystem']['ftp']['host'])
                ->addArgument(array(
                    'port' => $config['filesystem']['ftp']['port'],
                    'username' => $config['filesystem']['ftp']['username'],
                    'password' => $config['filesystem']['ftp']['password'],
                    'passive' => $config['filesystem']['ftp']['passive'],
                    'create' => $config['filesystem']['ftp']['create'],
                    'mode' => $config['filesystem']['ftp']['mode']
                ))
            ;
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.ftp');
            $container->removeDefinition('sonata.media.filesystem.ftp');
        }

        // add the default configuration for the S3 filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.s3') && isset($config['filesystem']['s3'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.s3')
                ->replaceArgument(0, new Reference('sonata.media.adapter.service.s3'))
                ->replaceArgument(1, $config['filesystem']['s3']['bucket'])
                ->replaceArgument(2, array('create' => $config['filesystem']['s3']['create'], 'region' => $config['filesystem']['s3']['region']))
                ->addMethodCall('setDirectory', array($config['filesystem']['s3']['directory']));
            ;

            $container->getDefinition('sonata.media.metadata.amazon')
                ->addArgument(array(
                        'acl' => $config['filesystem']['s3']['acl'],
                        'storage' => $config['filesystem']['s3']['storage'],
                        'encryption' => $config['filesystem']['s3']['encryption'],
                        'meta' => $config['filesystem']['s3']['meta'],
                        'cache_control' => $config['filesystem']['s3']['cache_control']
                ))
            ;

            $container->getDefinition('sonata.media.adapter.service.s3')
                ->replaceArgument(0, array(
                    'secret' => $config['filesystem']['s3']['secretKey'],
                    'key'    => $config['filesystem']['s3']['accessKey'],
                ))
            ;
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.s3');
            $container->removeDefinition('sonata.media.filesystem.s3');
        }

        if ($container->hasDefinition('sonata.media.adapter.filesystem.replicate') && isset($config['filesystem']['replicate'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.replicate')
                ->replaceArgument(0, new Reference($config['filesystem']['replicate']['master']))
                ->replaceArgument(1, new Reference($config['filesystem']['replicate']['slave']))
            ;
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.replicate');
            $container->removeDefinition('sonata.media.filesystem.replicate');
        }

        if ($container->hasDefinition('sonata.media.adapter.filesystem.mogilefs') && isset($config['filesystem']['mogilefs'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.mogilefs')
                ->replaceArgument(0, $config['filesystem']['mogilefs']['domain'])
                ->replaceArgument(1, $config['filesystem']['mogilefs']['hosts'])
            ;
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.mogilefs');
            $container->removeDefinition('sonata.media.filesystem.mogilefs');
        }

        if($container->hasDefinition('sonata.media.adapter.filesystem.opencloud') &&
            (isset($config['filesystem']['openstack']) || isset($config['filesystem']['rackspace']))) {
            if(isset($config['filesystem']['openstack'])) {
                $container->setParameter('sonata.media.adapter.filesystem.opencloud.class', 'OpenCloud\OpenStack');
                $settings = 'openstack';
            } else {
                $container->setParameter('sonata.media.adapter.filesystem.opencloud.class', 'OpenCloud\Rackspace');
                $settings = 'rackspace';
            }
            $container->getDefinition('sonata.media.adapter.filesystem.opencloud.connection')
                ->replaceArgument(0, $config['filesystem'][$settings]['url'])
                ->replaceArgument(1, $config['filesystem'][$settings]['secret'])
                ;
            $container->getDefinition('sonata.media.adapter.filesystem.opencloud')
                ->replaceArgument(1, $config['filesystem'][$settings]['containerName'])
                ->replaceArgument(2, $config['filesystem'][$settings]['create_container']);
            $container->getDefinition('sonata.media.adapter.filesystem.opencloud.objectstore')
                ->replaceArgument(1, $config['filesystem'][$settings]['region']);
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud');
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.connection');
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.objectstore');
            $container->removeDefinition('sonata.media.filesystem.opencloud');
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array                                                   $config
     */
    public function configureExtra(ContainerBuilder $container, array $config)
    {
        if ($config['pixlr']['enabled']) {
            $container->getDefinition('sonata.media.extra.pixlr')
                ->replaceArgument(0, $config['pixlr']['referrer'])
                ->replaceArgument(1, $config['pixlr']['secret'])
            ;
        } else {
            $container->removeDefinition('sonata.media.extra.pixlr');
        }
    }

    /**
     * Add class to compile
     */
    public function configureClassesToCompile()
    {
        $this->addClassesToCompile(array(
            "Sonata\\MediaBundle\\CDN\\CDNInterface",
            "Sonata\\MediaBundle\\CDN\\Fallback",
            "Sonata\\MediaBundle\\CDN\\PantherPortal",
            "Sonata\\MediaBundle\\CDN\\Server",
            "Sonata\\MediaBundle\\Extra\\Pixlr",
            "Sonata\\MediaBundle\\Filesystem\\Local",
            "Sonata\\MediaBundle\\Filesystem\\Replicate",
            "Sonata\\MediaBundle\\Generator\\DefaultGenerator",
            "Sonata\\MediaBundle\\Generator\\GeneratorInterface",
            "Sonata\\MediaBundle\\Generator\\ODMGenerator",
            "Sonata\\MediaBundle\\Generator\\PHPCRGenerator",
            "Sonata\\MediaBundle\\Metadata\\AmazonMetadataBuilder",
            "Sonata\\MediaBundle\\Metadata\\MetadataBuilderInterface",
            "Sonata\\MediaBundle\\Metadata\\NoopMetadataBuilder",
            "Sonata\\MediaBundle\\Metadata\\ProxyMetadataBuilder",
            "Sonata\\MediaBundle\\Model\\Gallery",
            "Sonata\\MediaBundle\\Model\\GalleryHasMedia",
            "Sonata\\MediaBundle\\Model\\GalleryHasMediaInterface",
            "Sonata\\MediaBundle\\Model\\GalleryInterface",
            "Sonata\\MediaBundle\\Model\\GalleryManager",
            "Sonata\\MediaBundle\\Model\\GalleryManagerInterface",
            "Sonata\\MediaBundle\\Model\\Media",
            "Sonata\\MediaBundle\\Model\\MediaInterface",
            "Sonata\\MediaBundle\\Model\\MediaManager",
            "Sonata\\MediaBundle\\Model\\MediaManagerInterface",
            "Sonata\\MediaBundle\\Provider\\BaseProvider",
            "Sonata\\MediaBundle\\Provider\\BaseVideoProvider",
            "Sonata\\MediaBundle\\Provider\\DailyMotionProvider",
            "Sonata\\MediaBundle\\Provider\\FileProvider",
            "Sonata\\MediaBundle\\Provider\\ImageProvider",
            "Sonata\\MediaBundle\\Provider\\MediaProviderInterface",
            "Sonata\\MediaBundle\\Provider\\Pool",
            "Sonata\\MediaBundle\\Provider\\VimeoProvider",
            "Sonata\\MediaBundle\\Provider\\YouTubeProvider",
            "Sonata\\MediaBundle\\Resizer\\ResizerInterface",
            "Sonata\\MediaBundle\\Resizer\\SimpleResizer",
            "Sonata\\MediaBundle\\Resizer\\SquareResizer",
            "Sonata\\MediaBundle\\Security\\DownloadStrategyInterface",
            "Sonata\\MediaBundle\\Security\\ForbiddenDownloadStrategy",
            "Sonata\\MediaBundle\\Security\\PublicDownloadStrategy",
            "Sonata\\MediaBundle\\Security\\RolesDownloadStrategy",
            "Sonata\\MediaBundle\\Security\\SessionDownloadStrategy",
            "Sonata\\MediaBundle\\Templating\\Helper\\MediaHelper",
            "Sonata\\MediaBundle\\Thumbnail\\ConsumerThumbnail",
            "Sonata\\MediaBundle\\Thumbnail\\FormatThumbnail",
            "Sonata\\MediaBundle\\Thumbnail\\ThumbnailInterface",
            "Sonata\\MediaBundle\\Twig\\Extension\\MediaExtension",
            "Sonata\\MediaBundle\\Twig\\Node\\MediaNode",
            "Sonata\\MediaBundle\\Twig\\Node\\PathNode",
            "Sonata\\MediaBundle\\Twig\\Node\\ThumbnailNode",
        ));
    }
}
