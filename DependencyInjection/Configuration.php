<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sonata_media')->children();

        $node
            ->scalarNode('db_driver')->isRequired()->end()
            ->scalarNode('default_context')->isRequired()->end()
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
                            ->addDefaultsIfNotSet()
                            ->prototype('scalar')
                                ->defaultValue(array())
                            ->end()
                        ->end()
                        ->arrayNode('formats')
                            ->isRequired()
                            ->useAttributeAsKey('id')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('width')->defaultValue(false)->end()
                                    ->scalarNode('height')->defaultValue(false)->end()
                                    ->scalarNode('quality')->defaultValue(80)->end()
                                    ->scalarNode('format')->defaultValue('jpg')->end()
                                    ->scalarNode('constraint')->defaultValue(true)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('cdn')
                ->useAttributeAsKey('id')
                ->children()
                    ->arrayNode('server')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('path')->defaultValue('/uploads/media')->end()
                        ->end()
                    ->end()

                    ->arrayNode('panther')
                        ->children()
                            ->scalarNode('path')->isRequired()->end() // http://domain.pantherportal.com/uploads/media
                            ->scalarNode('site_id')->isRequired()->end()
                            ->scalarNode('password')->isRequired()->end()
                            ->scalarNode('username')->isRequired()->end()
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

            ->arrayNode('filesystem')
                ->useAttributeAsKey('id')
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
                        ->end()
                    ->end()

                    ->arrayNode('s3')
                        ->children()
                            ->scalarNode('bucket')->isRequired()->end()
                            ->scalarNode('accessKey')->isRequired()->end()
                            ->scalarNode('secretKey')->isRequired()->end()
                            ->scalarNode('password')->isRequired()->end()
                            ->scalarNode('create')->defaultValue(false)->end()
                        ->end()
                    ->end()

                    ->arrayNode('replicate')
                        ->children()
                            ->scalarNode('master')->isRequired()->end()
                            ->scalarNode('slave')->isRequired()->end()
                        ->end()
                    ->end()

                ->end()
            ->end()

            ->arrayNode('providers')
                ->addDefaultsIfNotSet()
                ->useAttributeAsKey('id')
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
                        ->end()
                    ->end()

                    ->arrayNode('image')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('service')->defaultValue('sonata.media.provider.image')->end()
                            ->scalarNode('resizer')->defaultValue('sonata.media.resizer.simple')->end()
                            ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                            ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                            ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                            ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                        ->end()
                    ->end()

                    ->arrayNode('youtube')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('service')->defaultValue('sonata.media.provider.youtube')->end()
                            ->scalarNode('resizer')->defaultValue('sonata.media.resizer.simple')->end()
                            ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                            ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                            ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                            ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                        ->end()
                    ->end()

                    ->arrayNode('dailymotion')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('service')->defaultValue('sonata.media.provider.dailymotion')->end()
                            ->scalarNode('resizer')->defaultValue('sonata.media.resizer.simple')->end()
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
                            ->scalarNode('resizer')->defaultValue('sonata.media.resizer.simple')->end()
                            ->scalarNode('filesystem')->defaultValue('sonata.media.filesystem.local')->end()
                            ->scalarNode('cdn')->defaultValue('sonata.media.cdn.server')->end()
                            ->scalarNode('generator')->defaultValue('sonata.media.generator.default')->end()
                            ->scalarNode('thumbnail')->defaultValue('sonata.media.thumbnail.format')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

            ->arrayNode('class')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Media')->end()
                    ->scalarNode('gallery')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\Gallery')->end()
                    ->scalarNode('gallery_has_media')->defaultValue('Application\\Sonata\\MediaBundle\\Entity\\GalleryHasMedia')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
