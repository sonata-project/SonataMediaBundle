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

namespace Sonata\MediaBundle\DependencyInjection;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SonataMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array
     */
    private $bundleConfigs;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('provider.xml');
        $loader->load('media.xml');
        $loader->load('twig.xml');
        $loader->load('security.xml');
        $loader->load('extra.xml');
        $loader->load('form.xml');
        $loader->load('gaufrette.xml');
        $loader->load('validators.xml');
        $loader->load('serializer.xml');
        $loader->load('command.xml');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['FOSRestBundle'], $bundles['NelmioApiDocBundle'])) {
            $loader->load(sprintf('api_form_%s.xml', $config['db_driver']));

            if ('doctrine_orm' === $config['db_driver']) {
                $loader->load('api_controllers.xml');
            }
        }

        if (isset($bundles['SonataNotificationBundle'])) {
            $loader->load('consumer.xml');
        }

        if (isset($bundles['SonataFormatterBundle'])) {
            $loader->load('formatter.xml');
        }

        if (isset($bundles['SonataBlockBundle'])) {
            $loader->load('block.xml');
        }

        if (isset($bundles['SonataSeoBundle'])) {
            $loader->load('seo_block.xml');
        }

        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('sonata.media.thumbnail.liip_imagine');
        }

        if ($this->isClassificationEnabled($config)) {
            $loader->load('category.xml');
            $categoryManagerAlias = 'sonata.media.manager.category';
            $container->setAlias($categoryManagerAlias, $config['category_manager']);
            $categoryManager = $container->getAlias($categoryManagerAlias);
            $categoryManager->setPublic(true);
        }

        if (!\array_key_exists($config['default_context'], $config['contexts'])) {
            throw new \InvalidArgumentException(sprintf('SonataMediaBundle - Invalid default context : %s, available : %s', $config['default_context'], json_encode(array_keys($config['contexts']))));
        }

        $loader->load(sprintf('%s.xml', $config['db_driver']));

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load(sprintf('%s_admin.xml', $config['db_driver']));

            $sonataAdminConfig = $this->bundleConfigs['SonataAdminBundle'];

            $sonataRoles = [];
            if (isset($sonataAdminConfig['security']['role_admin'])) {
                $sonataRoles[] = $sonataAdminConfig['security']['role_admin'];
            } else {
                $sonataRoles[] = 'ROLE_ADMIN';
            }

            if (isset($sonataAdminConfig['security']['role_super_admin'])) {
                $sonataRoles[] = $sonataAdminConfig['security']['role_super_admin'];
            } else {
                $sonataRoles[] = 'ROLE_SUPER_ADMIN';
            }

            $container->getDefinition('sonata.media.security.superadmin_strategy')
                ->replaceArgument(2, $sonataRoles);
        }

        $this->configureFilesystemAdapter($container, $config);
        $this->configureCdnAdapter($container, $config);

        $pool = $container->getDefinition('sonata.media.pool');
        $pool->replaceArgument(0, $config['default_context']);

        $strategies = [];

        foreach ($config['contexts'] as $name => $settings) {
            $formats = [];

            foreach ($settings['formats'] as $format => $value) {
                $formats[$name.'_'.$format] = $value;
            }

            $strategies[] = $settings['download']['strategy'];
            $pool->addMethodCall('addContext', [$name, $settings['providers'], $formats, $settings['download']]);
        }

        $container->setParameter('sonata.media.admin_format', $config['admin_format']);

        $strategies = array_unique($strategies);

        foreach ($strategies as $strategyId) {
            $pool->addMethodCall('addDownloadStrategy', [$strategyId, new Reference($strategyId)]);
        }

        if ('doctrine_orm' === $config['db_driver']) {
            $this->registerDoctrineMapping($config);
        }

        $container->setParameter('sonata.media.resizer.simple.adapter.mode', $config['resizer']['simple']['mode']);
        $container->setParameter('sonata.media.resizer.square.adapter.mode', $config['resizer']['square']['mode']);

        $this->configureParameterClass($container, $config);
        $this->configureExtra($container, $config);
        $this->configureBuzz($container, $config);
        $this->configureProviders($container, $config);
        $this->configureAdapters($container, $config);
        $this->configureResizers($container, $config);
    }

    public function configureProviders(ContainerBuilder $container, array $config)
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

    public function configureBuzz(ContainerBuilder $container, array $config)
    {
        $container->getDefinition('sonata.media.buzz.browser')
            ->replaceArgument(0, new Reference($config['buzz']['connector']));

        foreach ([
            'sonata.media.buzz.connector.curl',
            'sonata.media.buzz.connector.file_get_contents',
        ] as $connector) {
            $container->getDefinition($connector)
                ->addMethodCall('setIgnoreErrors', [$config['buzz']['client']['ignore_errors']])
                ->addMethodCall('setMaxRedirects', [$config['buzz']['client']['max_redirects']])
                ->addMethodCall('setTimeout', [$config['buzz']['client']['timeout']])
                ->addMethodCall('setVerifyPeer', [$config['buzz']['client']['verify_peer']])
                ->addMethodCall('setProxy', [$config['buzz']['client']['proxy']]);
        }
    }

    public function configureParameterClass(ContainerBuilder $container, array $config)
    {
        $container->setParameter('sonata.media.admin.media.entity', $config['class']['media']);
        $container->setParameter('sonata.media.admin.gallery.entity', $config['class']['gallery']);
        $container->setParameter('sonata.media.admin.gallery_has_media.entity', $config['class']['gallery_has_media']);

        $container->setParameter('sonata.media.media.class', $config['class']['media']);
        $container->setParameter('sonata.media.gallery.class', $config['class']['gallery']);

        $container->getDefinition('sonata.media.form.type.media')->replaceArgument(1, $config['class']['media']);
    }

    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['class']['media'], 'mapOneToMany', [
            'fieldName' => 'galleryHasMedias',
            'targetEntity' => $config['class']['gallery_has_media'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => 'media',
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', [
            'fieldName' => 'gallery',
            'targetEntity' => $config['class']['gallery'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'galleryHasMedias',
            'joinColumns' => [
                [
                    'name' => 'gallery_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['gallery_has_media'], 'mapManyToOne', [
            'fieldName' => 'media',
            'targetEntity' => $config['class']['media'],
            'cascade' => [
                 'persist',
            ],
            'mappedBy' => null,
            'inversedBy' => 'galleryHasMedias',
            'joinColumns' => [
                [
                    'name' => 'media_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ],
            ],
            'orphanRemoval' => false,
        ]);

        $collector->addAssociation($config['class']['gallery'], 'mapOneToMany', [
            'fieldName' => 'galleryHasMedias',
            'targetEntity' => $config['class']['gallery_has_media'],
            'cascade' => [
                'persist',
            ],
            'mappedBy' => 'gallery',
            'orphanRemoval' => true,
            'orderBy' => [
                'position' => 'ASC',
            ],
        ]);

        if ($this->isClassificationEnabled($config)) {
            $collector->addAssociation($config['class']['media'], 'mapManyToOne', [
                'fieldName' => 'category',
                'targetEntity' => $config['class']['category'],
                'cascade' => [
                    'persist',
                ],
                'mappedBy' => null,
                'inversedBy' => null,
                'joinColumns' => [
                    [
                     'name' => 'category_id',
                     'referencedColumnName' => 'id',
                     'onDelete' => 'SET NULL',
                    ],
                ],
                'orphanRemoval' => false,
            ]);
        }
    }

    /**
     * Inject CDN dependency to default provider.
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

        if ($container->hasDefinition('sonata.media.cdn.cloudfront') && isset($config['cdn']['cloudfront'])) {
            $container->getDefinition('sonata.media.cdn.cloudfront')
                ->replaceArgument(0, $config['cdn']['cloudfront']['path'])
                ->replaceArgument(1, $config['cdn']['cloudfront']['key'])
                ->replaceArgument(2, $config['cdn']['cloudfront']['secret'])
                ->replaceArgument(3, $config['cdn']['cloudfront']['distribution_id'])
            ;
        } else {
            $container->removeDefinition('sonata.media.cdn.cloudfront');
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
     * Inject filesystem dependency to default provider.
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
                ->addArgument([
                    'port' => $config['filesystem']['ftp']['port'],
                    'username' => $config['filesystem']['ftp']['username'],
                    'password' => $config['filesystem']['ftp']['password'],
                    'passive' => $config['filesystem']['ftp']['passive'],
                    'create' => $config['filesystem']['ftp']['create'],
                    'mode' => $config['filesystem']['ftp']['mode'],
                ])
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
                ->replaceArgument(2, ['create' => $config['filesystem']['s3']['create'], 'region' => $config['filesystem']['s3']['region'], 'directory' => $config['filesystem']['s3']['directory'], 'ACL' => $config['filesystem']['s3']['acl']])
            ;

            $container->getDefinition('sonata.media.metadata.amazon')
                ->replaceArgument(0, [
                        'acl' => $config['filesystem']['s3']['acl'],
                        'storage' => $config['filesystem']['s3']['storage'],
                        'encryption' => $config['filesystem']['s3']['encryption'],
                        'meta' => $config['filesystem']['s3']['meta'],
                        'cache_control' => $config['filesystem']['s3']['cache_control'],
                ])
            ;

            if (3 === $config['filesystem']['s3']['sdk_version']) {
                $arguments = [
                    'region' => $config['filesystem']['s3']['region'],
                    'version' => $config['filesystem']['s3']['version'],
                ];

                if (isset($config['filesystem']['s3']['secretKey'], $config['filesystem']['s3']['accessKey'])) {
                    $arguments['credentials'] = [
                        'secret' => $config['filesystem']['s3']['secretKey'],
                        'key' => $config['filesystem']['s3']['accessKey'],
                    ];
                }

                $container->getDefinition('sonata.media.adapter.service.s3')
                    ->replaceArgument(0, $arguments)
                ;
            } else {
                $container->getDefinition('sonata.media.adapter.service.s3')
                    ->replaceArgument(0, [
                        'secret' => $config['filesystem']['s3']['secretKey'],
                        'key' => $config['filesystem']['s3']['accessKey'],
                    ])
                ;
            }
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

        if ($container->hasDefinition('sonata.media.adapter.filesystem.opencloud') &&
            (isset($config['filesystem']['openstack']) || isset($config['filesystem']['rackspace']))) {
            if (isset($config['filesystem']['openstack'])) {
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
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.22, to be removed in version 4.0.
     */
    public function configureClassesToCompile()
    {
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        // Store SonataAdminBundle configuration for later use
        if (isset($bundles['SonataAdminBundle'])) {
            $this->bundleConfigs['SonataAdminBundle'] = current($container->getExtensionConfig('sonata_admin'));
        }
    }

    /**
     * Checks if the classification of media is enabled.
     */
    private function isClassificationEnabled(array $config): bool
    {
        return interface_exists(CategoryInterface::class)
            && !$config['force_disable_category'];
    }

    private function configureAdapters(ContainerBuilder $container, array $config): void
    {
        foreach (['gd', 'imagick', 'gmagick'] as $adapter) {
            if ($container->hasParameter('sonata.media.adapter.image.'.$adapter.'.class')) {
                $container->register(
                    'sonata.media.adapter.image.'.$adapter,
                    $container->getParameter('sonata.media.adapter.image.'.$adapter.'.class')
                );
            }
        }
        $container->setAlias('sonata.media.adapter.image.default', $config['adapters']['default']);
    }

    private function configureResizers(ContainerBuilder $container, array $config): void
    {
        if ($container->hasParameter('sonata.media.resizer.simple.class')) {
            $class = $container->getParameter('sonata.media.resizer.simple.class');
            $definition = new Definition($class, [
                new Reference('sonata.media.adapter.image.default'),
                '%sonata.media.resizer.simple.adapter.mode%',
                new Reference('sonata.media.metadata.proxy'),
            ]);
            $definition->addTag('sonata.media.resizer');
            $container->setDefinition('sonata.media.resizer.simple', $definition);
        }

        if ($container->hasParameter('sonata.media.resizer.square.class')) {
            $class = $container->getParameter('sonata.media.resizer.square.class');
            $definition = new Definition($class, [
                new Reference('sonata.media.adapter.image.default'),
                '%sonata.media.resizer.square.adapter.mode%',
                new Reference('sonata.media.metadata.proxy'),
            ]);
            $definition->addTag('sonata.media.resizer');
            $container->setDefinition('sonata.media.resizer.square', $definition);
        }

        $container->setAlias('sonata.media.resizer.default', $config['resizers']['default']);
    }
}
