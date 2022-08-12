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

use Imagine\Image\ManipulatorInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
final class Configuration implements ConfigurationInterface
{
    private const DB_DRIVERS = ['doctrine_orm', 'doctrine_mongodb', 'no_driver'];

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sonata_media');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('db_driver')
                    ->defaultValue('no_driver')
                    ->info('Choose persistence mechanism driver from the following list: "doctrine_orm", "doctrine_mongodb"')
                    ->validate()
                        ->ifNotInArray(self::DB_DRIVERS)
                        ->thenInvalid('SonataMediaBundle - Invalid db driver %s.')
                    ->end()
                ->end()
                ->scalarNode('default_context')->isRequired()->end()
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
                        ->scalarNode('resizer')->defaultNull()->end()
                        ->arrayNode('resizer_options')
                            ->info('options directly passed to selected resizer. e.g. {use_crop: true, crop_gravity: center}')
                            ->defaultValue([])
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();

        $this->addContextsSection($rootNode);
        $this->addCdnSection($rootNode);
        $this->addFilesystemSection($rootNode);
        $this->addProvidersSection($rootNode);
        $this->addModelSection($rootNode);
        $this->addHttpClientSection($rootNode);
        $this->addResizerSection($rootNode);
        $this->addAdapterSection($rootNode);
        $this->addMessengerSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addContextsSection(ArrayNodeDefinition $node): void
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
                                        ->integerNode('width')->defaultValue(null)->end()
                                        ->integerNode('height')->defaultValue(null)->end()
                                        ->integerNode('quality')->defaultValue(80)->end()
                                        ->scalarNode('format')->defaultValue('jpg')->end()
                                        ->booleanNode('constraint')->defaultValue(true)->end()
                                        ->scalarNode('resizer')->defaultNull()->end()
                                        ->arrayNode('resizer_options')
                                            ->info('options directly passed to selected resizer. e.g. {use_crop: true, crop_gravity: center}')
                                            ->defaultValue([])
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addCdnSection(ArrayNodeDefinition $node): void
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

                        ->arrayNode('cloudfront')
                            ->children()
                                ->scalarNode('path')
                                    ->info('e.g. http://xxxxxxxxxxxxxx.cloudfront.net/uploads/media')
                                    ->isRequired()
                                ->end()
                                ->scalarNode('distribution_id')->isRequired()->end()
                                ->scalarNode('key')->isRequired()->end()
                                ->scalarNode('secret')->isRequired()->end()
                                ->scalarNode('region')->isRequired()->end()
                                ->scalarNode('version')->isRequired()->end()
                            ->end()
                        ->end()

                        ->arrayNode('fallback')
                            ->children()
                                ->scalarNode('primary')->isRequired()->end()
                                ->scalarNode('fallback')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addFilesystemSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('filesystem')
                    ->children()
                        ->arrayNode('local')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('directory')->defaultValue('%kernel.project_dir%/web/uploads/media')->end()
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
                                ->scalarNode('mode')->defaultValue(\defined('FTP_BINARY') ? \FTP_BINARY : false)->end()
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
                                ->scalarNode('endpoint')->defaultNull()->end()
                                ->scalarNode('version')
                                    ->info(
                                        'Using "latest" in a production application is not recommended because pulling in a new minor version of the SDK'
                                        .' that includes an API update could break your production application.'
                                        .' See https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_configuration.html#cfg-version.'
                                    )
                                    ->defaultValue('latest')
                                ->end()
                                ->arrayNode('meta')
                                    ->useAttributeAsKey('name')
                                    ->prototype('scalar')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()

                        ->arrayNode('replicate')
                            ->children()
                                ->scalarNode('primary')->isRequired()->end()
                                ->scalarNode('secondary')->isRequired()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addProvidersSection(ArrayNodeDefinition $node): void
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
                                ->scalarNode('resizer')->defaultNull()->end()
                                ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                                ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                                ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                                ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.file')->end()
                                ->arrayNode('allowed_extensions')
                                    ->beforeNormalization()
                                    ->ifArray()
                                        ->then(static fn (array $allowedExtensions): array => array_map('strtolower', $allowedExtensions))
                                    ->end()
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
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static fn (array $allowedMimeTypes): array => array_map('strtolower', $allowedMimeTypes))
                                    ->end()
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
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static fn (array $allowedExtensions): array => array_map('strtolower', $allowedExtensions))
                                    ->end()
                                    ->prototype('scalar')->end()
                                    ->defaultValue(['jpg', 'png', 'jpeg'])
                                ->end()
                                ->arrayNode('allowed_mime_types')
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static fn (array $allowedMimeTypes): array => array_map('strtolower', $allowedMimeTypes))
                                    ->end()
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
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addModelSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('media')->defaultValue('App\\Entity\\SonataMediaMedia')->end()
                        ->scalarNode('gallery')->defaultValue('App\\Entity\\SonataMediaGallery')->end()
                        ->scalarNode('gallery_item')->defaultValue('App\\Entity\\SonataMediaGalleryItem')->end()
                        ->scalarNode('category')->defaultValue('App\\Entity\\SonataClassificationCategory')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addHttpClientSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('http')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('client')
                            ->defaultValue('sonata.media.http.base_client')
                            ->info('Alias of the http client.')
                        ->end()
                        ->scalarNode('message_factory')
                            ->defaultValue('sonata.media.http.base_message_factory')
                            ->info('Alias of the message factory.')
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addResizerSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('resizer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('simple')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('mode')->defaultValue(ManipulatorInterface::THUMBNAIL_INSET)->end()
                            ->end()
                        ->end()
                        ->arrayNode('square')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->integerNode('mode')->defaultValue(ManipulatorInterface::THUMBNAIL_INSET)->end()
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
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addAdapterSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('adapters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default')->defaultValue('sonata.media.adapter.image.gd')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @psalm-suppress PossiblyNullReference, PossiblyUndefinedMethod
     *
     * @see https://github.com/psalm/psalm-plugin-symfony/issues/174
     */
    private function addMessengerSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('messenger')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('generate_thumbnails_bus')->isRequired()->defaultValue('messenger.default_bus')->end()
                    ->end()
                ->end()
            ->end();
    }
}
