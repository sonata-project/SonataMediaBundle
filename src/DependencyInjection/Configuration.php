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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * NEXT_MAJOR: make constant protected/private.
     */
    public const DB_DRIVERS = ['doctrine_orm', 'doctrine_mongodb', 'doctrine_phpcr', 'no_driver'];

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sonata_media');

        // Keep compatibility with symfony/config < 4.2
        if (!method_exists($treeBuilder, 'getRootNode')) {
            $node = $treeBuilder->root('sonata_media');
        } else {
            $node = $treeBuilder->getRootNode();
        }

        $node
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('no_driver')
                    ->info('Choose persistence mechanism driver from the following list: "doctrine_orm", "doctrine_mongodb", "doctrine_phpcr"')
                    ->validate()
                        ->ifNotInArray(self::DB_DRIVERS)
                        ->thenInvalid('SonataMediaBundle - Invalid db driver %s.')
                    ->end()
                ->end()
                ->scalarNode('default_context')->isRequired()->end()
                ->scalarNode('category_manager')
                    ->defaultValue('sonata.media.manager.category.default')
                ->end()
                ->booleanNode('force_disable_category')
                    ->info('true IF you really want to disable the relation with category')
                    ->defaultFalse()
                ->end()
                ->arrayNode('admin_format')
                    ->info('Configures the thumbnail preview for the admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('width')->defaultValue(200)->end()
                        ->scalarNode('height')->defaultValue(null)->end()
                        ->scalarNode('quality')->defaultValue(90)->end()
                        ->scalarNode('format')->defaultValue('jpg')->end()
                        ->scalarNode('constraint')->defaultValue(true)->end()
                    ->end()
                ->end()
            ->end()
        ;

        $this->addContextsSection($node);
        $this->addCdnSection($node);
        $this->addFilesystemSection($node);
        $this->addProvidersSection($node);
        $this->addExtraSection($node);
        $this->addModelSection($node);
        $this->addBuzzSection($node);
        $this->addResizerSection($node);
        $this->addAdapterSection($node);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addContextsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('contexts')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('download')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('strategy')->defaultValue('sonata.media.security.superadmin_strategy')->end()
                                    ->scalarNode('mode')->defaultValue('http')->end()
                                ->end()
                            ->end()
                            ->arrayNode('providers')
                                ->prototype('scalar')
                                    ->defaultValue([])
                                ->end()
                            ->end()
                            ->arrayNode('formats')
                                ->useAttributeAsKey('id')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('width')->defaultValue(null)->end()
                                        ->scalarNode('height')->defaultValue(null)->end()
                                        ->scalarNode('quality')->defaultValue(80)->end()
                                        ->scalarNode('format')->defaultValue('jpg')->end()
                                        ->scalarNode('constraint')->defaultValue(true)->end()
                                        ->scalarNode('resizer')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addCdnSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('cdn')
                    ->children()
                        ->arrayNode('server')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('path')->defaultValue('/uploads/media')->end()
                            ->end()
                        ->end()

                        ->arrayNode('panther')
                            ->children()
                                ->scalarNode('path')
                                    ->info('e.g. http://domain.pantherportal.com/uploads/media')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('site_id')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->scalarNode('username')->isRequired()->end()
                            ->end()
                        ->end()

                        ->arrayNode('cloudfront')
                            ->children()
                                ->scalarNode('path')
                                    ->info('e.g. http://xxxxxxxxxxxxxx.cloudfront.net/uploads/media')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('distribution_id')->isRequired()->end()
                                ->scalarNode('key')->isRequired()->end()
                                ->scalarNode('secret')->isRequired()->end()
                            ->end()
                        ->end()

                        ->arrayNode('fallback')
                            ->children()
                                ->scalarNode('master')->isRequired()->end()
                                ->scalarNode('fallback')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addFilesystemSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('filesystem')
                    ->children()
                        ->arrayNode('local')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('directory')->defaultValue('%kernel.root_dir%/../web/uploads/media')->end()
                                ->scalarNode('create')->defaultValue(false)->end()
                            ->end()
                        ->end()

                        ->arrayNode('ftp')
                            ->children()
                                ->scalarNode('directory')->isRequired()->end()
                                ->scalarNode('host')->isRequired()->end()
                                ->scalarNode('username')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->scalarNode('port')->defaultValue(21)->end()
                                ->scalarNode('passive')->defaultValue(false)->end()
                                ->scalarNode('create')->defaultValue(false)->end()
                                ->scalarNode('mode')->defaultValue(\defined('FTP_BINARY') ? FTP_BINARY : false)->end()
                            ->end()
                        ->end()

                        ->arrayNode('s3')
                            ->children()
                                ->scalarNode('directory')->defaultValue('')->end()
                                ->scalarNode('bucket')->isRequired()->end()
                                ->scalarNode('accessKey')->isRequired()->end()
                                ->scalarNode('secretKey')->isRequired()->end()
                                ->scalarNode('create')->defaultValue(false)->end()
                                ->scalarNode('storage')
                                    ->defaultValue('standard')
                                    ->validate()
                                    ->ifNotInArray(['standard', 'reduced'])
                                        ->thenInvalid('Invalid storage type - "%s"')
                                    ->end()
                                ->end()
                                ->scalarNode('cache_control')->defaultValue('')->end()
                                ->scalarNode('acl')
                                    ->defaultValue('public')
                                    ->validate()
                                    ->ifNotInArray(['private', 'public', 'open', 'auth_read', 'owner_read', 'owner_full_control'])
                                        ->thenInvalid('Invalid acl permission - "%s"')
                                    ->end()
                                ->end()
                                ->scalarNode('encryption')
                                    ->defaultValue('')
                                    ->validate()
                                    ->ifNotInArray(['aes256'])
                                        ->thenInvalid('Invalid encryption type - "%s"')
                                    ->end()
                                ->end()
                                ->scalarNode('region')->defaultValue('s3.amazonaws.com')->end()
                                ->scalarNode('version')->defaultValue('latest')->end()
                                ->enumNode('sdk_version')
                                    ->values([2, 3])
                                    ->defaultValue(2)
                                ->end()
                                ->arrayNode('meta')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('mogilefs')
                            ->children()
                                ->scalarNode('domain')->isRequired()->end()
                                ->arrayNode('hosts')
                                    ->prototype('scalar')->end()
                                    ->isRequired()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('replicate')
                            ->children()
                                ->scalarNode('master')->isRequired()->end()
                                ->scalarNode('slave')->isRequired()->end()
                            ->end()
                        ->end()
                        ->arrayNode('openstack')
                            ->children()
                                ->scalarNode('url')->isRequired()->end()
                                ->arrayNode('secret')
                                    ->children()
                                        ->scalarNode('username')->isRequired()->end()
                                        ->scalarNode('password')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->scalarNode('region')->end()
                                ->scalarNode('containerName')->defaultValue('media')->end()
                                ->scalarNode('create_container')->defaultValue(false)->end()
                            ->end()
                        ->end()
                        ->arrayNode('rackspace')
                            ->children()
                                ->scalarNode('url')->isRequired()->end()
                                    ->arrayNode('secret')
                                        ->children()
                                            ->scalarNode('username')->isRequired()->end()
                                            ->scalarNode('apiKey')->isRequired()->end()
                                        ->end()
                                    ->end()
                                    ->scalarNode('region')->isRequired()->end()
                                    ->scalarNode('containerName')->defaultValue('media')->end()
                                    ->scalarNode('create_container')->defaultValue(false)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addProvidersSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('providers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('file')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.file')->end()
                                ->scalarNode('resizer')->defaultValue(false)->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                                ->arrayNode('allowed_extensions')
                                    ->prototype('scalar')->end()
                                    ->defaultValue([
                                        'pdf', 'txt', 'rtf',
                                        'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                                        'odt', 'odg', 'odp', 'ods', 'odc', 'odf', 'odb',
                                        'csv',
                                        'xml',
                                    ])
                                ->end()
                                ->arrayNode('allowed_mime_types')
                                    ->prototype('scalar')->end()
                                    ->defaultValue([
                                        'application/pdf', 'application/x-pdf', 'application/rtf', 'text/html', 'text/rtf', 'text/plain',
                                        'application/excel', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint',
                                        'application/vnd.ms-powerpoint', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.graphics', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.chart', 'application/vnd.oasis.opendocument.formula', 'application/vnd.oasis.opendocument.database', 'application/vnd.oasis.opendocument.image',
                                        'text/comma-separated-values',
                                        'text/xml', 'application/xml',
                                        'application/zip', // seems to be used for xlsx document ...
                                    ])
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('image')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.image')->end()
                                ->scalarNode('resizer')->defaultValue('sonata.media.resizer.default')->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                                ->scalarNode('adapter')->defaultValue('sonata.media.adapter.image.default')->end()
                                ->arrayNode('allowed_extensions')
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['jpg', 'png', 'jpeg'])
                                ->end()
                                ->arrayNode('allowed_mime_types')
                                    ->prototype('scalar')->end()
                                    ->defaultValue([
                                        'image/pjpeg',
                                        'image/jpeg',
                                        'image/png',
                                        'image/x-png',
                                    ])
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('youtube')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.youtube')->end()
                                ->scalarNode('resizer')->defaultValue('sonata.media.resizer.default')->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                                ->scalarNode('html5')->defaultValue(false)->end()
                            ->end()
                        ->end()

                        ->arrayNode('dailymotion')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.dailymotion')->end()
                                ->scalarNode('resizer')->defaultValue('sonata.media.resizer.default')->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                            ->end()
                        ->end()

                        ->arrayNode('vimeo')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('service')->defaultValue('sonata.media.provider.vimeo')->end()
                                ->scalarNode('resizer')->defaultValue('sonata.media.resizer.default')->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addExtraSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('pixlr')
                    ->info('More info at https://pixlr.com/')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultValue(false)->end()
                        ->scalarNode('secret')->defaultValue(sha1(uniqid((string) random_int(1, 9999), true)))->end()
                        ->scalarNode('referrer')->defaultValue('Sonata Media')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addModelSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Media')->end()
                        ->scalarNode('gallery')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Gallery')->end()
                        ->scalarNode('gallery_has_media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia')->end()
                        ->scalarNode('category')->defaultValue('Application\\Sonata\\ClassificationBundle\\Entity\\Category')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addBuzzSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('buzz')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('connector')->defaultValue('sonata.media.buzz.connector.curl')->end()
                        ->arrayNode('client')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->booleanNode('ignore_errors')->defaultValue(true)->end()
                            ->scalarNode('max_redirects')->defaultValue(5)->end()
                            ->scalarNode('timeout')->defaultValue(5)->end()
                            ->booleanNode('verify_peer')->defaultValue(true)->end()
                            ->scalarNode('proxy')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addResizerSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('resizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('simple')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('mode')->defaultValue('inset')->end()
                            ->end()
                        ->end()
                        ->arrayNode('square')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('mode')->defaultValue('inset')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('resizers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')->defaultValue('sonata.media.resizer.simple')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addAdapterSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('adapters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')->defaultValue('sonata.media.adapter.image.gd')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
