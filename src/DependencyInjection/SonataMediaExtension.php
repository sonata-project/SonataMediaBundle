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

use Sonata\Doctrine\Mapper\Builder\OptionsBuilder;
use Sonata\Doctrine\Mapper\DoctrineCollector;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class SonataMediaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array
     */
    private $bundleConfigs;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('provider.php');
        $loader->load('http_client.php');
        $loader->load('media.php');
        $loader->load('twig.php');
        $loader->load('security.php');
        $loader->load('extra.php');
        $loader->load('form.php');
        $loader->load('gaufrette.php');
        $loader->load('validators.php');
        $loader->load('serializer.php');
        $loader->load('command.php');

        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['FOSRestBundle'], $bundles['NelmioApiDocBundle'])) {
            $loader->load(sprintf('api_form_%s.php', $config['db_driver']));

            if ('doctrine_orm' === $config['db_driver']) {
                $loader->load('api_controllers.php');
            }
        }

        if (isset($bundles['SonataNotificationBundle'])) {
            $loader->load('consumer.php');
        }

        if (isset($bundles['SonataBlockBundle'])) {
            $loader->load('block.php');
        }

        if (isset($bundles['SonataSeoBundle'])) {
            $loader->load('seo_block.php');
        }

        if (!isset($bundles['LiipImagineBundle'])) {
            $container->removeDefinition('sonata.media.thumbnail.liip_imagine');
        }

        if ($this->isClassificationEnabled($bundles, $config)) {
            $loader->load('category.php');
        }

        if (!\array_key_exists($config['default_context'], $config['contexts'])) {
            throw new \InvalidArgumentException(sprintf('SonataMediaBundle - Invalid default context : %s, available : %s', $config['default_context'], json_encode(array_keys($config['contexts']))));
        }

        $loader->load(sprintf('%s.php', $config['db_driver']));

        if (isset($bundles['SonataAdminBundle'])) {
            $loader->load(sprintf('%s_admin.php', $config['db_driver']));

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
            if (!isset($bundles['SonataDoctrineBundle'])) {
                throw new \RuntimeException('You must register SonataDoctrineBundle to use SonataMediaBundle.');
            }

            $this->registerSonataDoctrineMapping($bundles, $config);
        }

        $container->setParameter('sonata.media.resizer.simple.adapter.mode', $config['resizer']['simple']['mode']);
        $container->setParameter('sonata.media.resizer.square.adapter.mode', $config['resizer']['square']['mode']);

        $this->configureParameterClass($container, $config);
        $this->configureExtra($container, $config);
        $this->configureHttpClient($container, $config);
        $this->configureProviders($container, $config);
        $this->configureAdapters($container, $config);
        $this->configureResizers($container, $config);
    }

    public function configureProviders(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition('sonata.media.provider.image')
            ->replaceArgument(5, array_map('strtolower', $config['providers']['image']['allowed_extensions']))
            ->replaceArgument(6, $config['providers']['image']['allowed_mime_types'])
            ->replaceArgument(7, new Reference($config['providers']['image']['adapter']));

        $container->getDefinition('sonata.media.provider.file')
            ->replaceArgument(5, $config['providers']['file']['allowed_extensions'])
            ->replaceArgument(6, $config['providers']['file']['allowed_mime_types']);

        $container->getDefinition('sonata.media.provider.youtube')->replaceArgument(8, $config['providers']['youtube']['html5']);
    }

    public function configureParameterClass(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('sonata.media.admin.media.entity', $config['class']['media']);
        $container->setParameter('sonata.media.admin.gallery.entity', $config['class']['gallery']);
        $container->setParameter('sonata.media.admin.gallery_item.entity', $config['class']['gallery_item']);

        $container->setParameter('sonata.media.media.class', $config['class']['media']);
        $container->setParameter('sonata.media.gallery.class', $config['class']['gallery']);

        $container->getDefinition('sonata.media.form.type.media')->replaceArgument(1, $config['class']['media']);
    }

    /**
     * Inject CDN dependency to default provider.
     */
    public function configureCdnAdapter(ContainerBuilder $container, array $config): void
    {
        // add the default configuration for the server cdn
        if ($container->hasDefinition('sonata.media.cdn.server') && isset($config['cdn']['server'])) {
            $container->getDefinition('sonata.media.cdn.server')
                ->replaceArgument(0, $config['cdn']['server']['path']);
        } else {
            $container->removeDefinition('sonata.media.cdn.server');
        }

        if ($container->hasDefinition('sonata.media.cdn.panther') && isset($config['cdn']['panther'])) {
            $container->getDefinition('sonata.media.cdn.panther')
                ->replaceArgument(0, $config['cdn']['panther']['path'])
                ->replaceArgument(1, $config['cdn']['panther']['username'])
                ->replaceArgument(2, $config['cdn']['panther']['password'])
                ->replaceArgument(3, $config['cdn']['panther']['site_id']);
        } else {
            $container->removeDefinition('sonata.media.cdn.panther');
        }

        if ($container->hasDefinition('sonata.media.cdn.cloudfront') && isset($config['cdn']['cloudfront'])) {
            if (isset($config['cdn']['cloudfront']['region'])) {
                $cloudFrontConfig['region'] = $config['cdn']['cloudfront']['region'];
            }

            if (isset($config['cdn']['cloudfront']['version'])) {
                $cloudFrontConfig['version'] = $config['cdn']['cloudfront']['version'];
            }

            $cloudFrontConfig['credentials'] = [
                'key' => $config['cdn']['cloudfront']['key'],
                'secret' => $config['cdn']['cloudfront']['secret'],
            ];

            $cloudFrontClass = CloudFrontVersion3::class;

            $container->getDefinition('sonata.media.cdn.cloudfront.client')
                    ->replaceArgument(0, $cloudFrontConfig);

            $container->getDefinition('sonata.media.cdn.cloudfront')
                ->setClass($cloudFrontClass)
                ->replaceArgument(0, new Reference('sonata.media.cdn.cloudfront.client'))
                ->replaceArgument(1, $config['cdn']['cloudfront']['distribution_id'])
                ->replaceArgument(2, $config['cdn']['cloudfront']['path']);
        } else {
            $container->removeDefinition('sonata.media.cdn.cloudfront.client');
            $container->removeDefinition('sonata.media.cdn.cloudfront');
        }

        if ($container->hasDefinition('sonata.media.cdn.fallback') && isset($config['cdn']['fallback'])) {
            $container->getDefinition('sonata.media.cdn.fallback')
                ->replaceArgument(0, new Reference($config['cdn']['fallback']['master']))
                ->replaceArgument(1, new Reference($config['cdn']['fallback']['fallback']));
        } else {
            $container->removeDefinition('sonata.media.cdn.fallback');
        }
    }

    /**
     * Inject filesystem dependency to default provider.
     */
    public function configureFilesystemAdapter(ContainerBuilder $container, array $config): void
    {
        // add the default configuration for the local filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.local') && isset($config['filesystem']['local'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.local')
                ->addArgument($config['filesystem']['local']['directory'])
                ->addArgument($config['filesystem']['local']['create']);
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
                ]);
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.ftp');
            $container->removeDefinition('sonata.media.filesystem.ftp');
        }

        // add the default configuration for the S3 filesystem
        if ($container->hasDefinition('sonata.media.adapter.filesystem.s3') && isset($config['filesystem']['s3'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.s3')
                ->replaceArgument(0, new Reference('sonata.media.adapter.service.s3'))
                ->replaceArgument(1, $config['filesystem']['s3']['bucket'])
                ->replaceArgument(2, ['create' => $config['filesystem']['s3']['create'], 'region' => $config['filesystem']['s3']['region'], 'directory' => $config['filesystem']['s3']['directory'], 'ACL' => $config['filesystem']['s3']['acl']]);

            $container->getDefinition('sonata.media.metadata.amazon')
                ->replaceArgument(0, [
                    'acl' => $config['filesystem']['s3']['acl'],
                    'storage' => $config['filesystem']['s3']['storage'],
                    'encryption' => $config['filesystem']['s3']['encryption'],
                    'meta' => $config['filesystem']['s3']['meta'],
                    'cache_control' => $config['filesystem']['s3']['cache_control'],
                ]);

            $arguments = [
                'region' => $config['filesystem']['s3']['region'],
                'version' => $config['filesystem']['s3']['version'],
            ];

            if (isset($config['filesystem']['s3']['endpoint'])) {
                $arguments['endpoint'] = $config['filesystem']['s3']['endpoint'];
            }

            if (isset($config['filesystem']['s3']['secretKey'], $config['filesystem']['s3']['accessKey'])) {
                $arguments['credentials'] = [
                    'secret' => $config['filesystem']['s3']['secretKey'],
                    'key' => $config['filesystem']['s3']['accessKey'],
                ];
            }

            $container->getDefinition('sonata.media.adapter.service.s3')
                ->replaceArgument(0, $arguments);
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.s3');
            $container->removeDefinition('sonata.media.filesystem.s3');
        }

        if ($container->hasDefinition('sonata.media.adapter.filesystem.replicate') && isset($config['filesystem']['replicate'])) {
            $container->getDefinition('sonata.media.adapter.filesystem.replicate')
                ->replaceArgument(0, new Reference($config['filesystem']['replicate']['master']))
                ->replaceArgument(1, new Reference($config['filesystem']['replicate']['slave']));
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.replicate');
            $container->removeDefinition('sonata.media.filesystem.replicate');
        }

        if ($container->hasDefinition('sonata.media.adapter.filesystem.opencloud') &&
            (isset($config['filesystem']['openstack']) || isset($config['filesystem']['rackspace']))) {
            if (isset($config['filesystem']['openstack'])) {
                $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.connection.rackspace');
                $settings = 'openstack';
            } else {
                $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.connection.openstack');
                $settings = 'rackspace';
            }

            $container->getDefinition(sprintf('sonata.media.adapter.filesystem.opencloud.connection.%s', $settings))
                ->replaceArgument(0, $config['filesystem'][$settings]['url'])
                ->replaceArgument(1, $config['filesystem'][$settings]['secret']);
            $container->getDefinition('sonata.media.adapter.filesystem.opencloud')
                ->replaceArgument(1, $config['filesystem'][$settings]['containerName'])
                ->replaceArgument(2, $config['filesystem'][$settings]['create_container']);
            $container->getDefinition('sonata.media.adapter.filesystem.opencloud.objectstore')
                ->replaceArgument(1, $config['filesystem'][$settings]['region'])
                ->setFactory([new Reference(sprintf('sonata.media.adapter.filesystem.opencloud.connection.%s', $settings)), 'ObjectStore']);
        } else {
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud');
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.connection.rackspace');
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.connection.openstack');
            $container->removeDefinition('sonata.media.adapter.filesystem.opencloud.objectstore');
            $container->removeDefinition('sonata.media.filesystem.opencloud');
        }
    }

    public function configureExtra(ContainerBuilder $container, array $config): void
    {
        if ($config['pixlr']['enabled']) {
            $container->getDefinition('sonata.media.extra.pixlr')
                ->replaceArgument(0, $config['pixlr']['referrer'])
                ->replaceArgument(1, $config['pixlr']['secret']);
        } else {
            $container->removeDefinition('sonata.media.extra.pixlr');
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     */
    public function prepend(ContainerBuilder $container): void
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
    private function isClassificationEnabled(array $bundles, array $config): bool
    {
        return isset($bundles['SonataClassificationBundle'])
            && !$config['force_disable_category'];
    }

    private function configureAdapters(ContainerBuilder $container, array $config): void
    {
        $container->setAlias('sonata.media.adapter.image.default', $config['adapters']['default']);
    }

    private function configureResizers(ContainerBuilder $container, array $config): void
    {
        $container->setAlias('sonata.media.resizer.default', $config['resizers']['default']);
    }

    private function registerSonataDoctrineMapping(array $bundles, array $config): void
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation(
            $config['class']['media'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('galleryItems', $config['class']['gallery_item'])
                ->cascade(['persist'])
                ->mappedBy('media')
        );

        $collector->addAssociation(
            $config['class']['gallery_item'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('gallery', $config['class']['gallery'])
                ->cascade(['persist'])
                ->inversedBy('galleryItems')
                ->addJoin([
                    'name' => 'gallery_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['gallery_item'],
            'mapManyToOne',
            OptionsBuilder::createManyToOne('media', $config['class']['media'])
                ->cascade(['persist'])
                ->inversedBy('galleryItems')
                ->addJoin([
                    'name' => 'media_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ])
        );

        $collector->addAssociation(
            $config['class']['gallery'],
            'mapOneToMany',
            OptionsBuilder::createOneToMany('galleryItems', $config['class']['gallery_item'])
                ->cascade(['persist'])
                ->mappedBy('gallery')
                ->orphanRemoval()
                ->addOrder('position', 'ASC')
        );

        if ($this->isClassificationEnabled($bundles, $config)) {
            $collector->addAssociation(
                $config['class']['media'],
                'mapManyToOne',
                OptionsBuilder::createManyToOne('category', $config['class']['category'])
                    ->cascade(['persist'])
                    ->addJoin([
                        'name' => 'category_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ])
            );
        }
    }

    private function configureHttpClient(ContainerBuilder $container, array $config): void
    {
        $container->setAlias('sonata.media.http.client', $config['http']['client']);
        $container->setAlias('sonata.media.http.message_factory', $config['http']['message_factory']);
    }
}
