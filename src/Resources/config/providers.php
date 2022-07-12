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

use Sonata\MediaBundle\Provider\DailyMotionProvider;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Provider\VimeoProvider;
use Sonata\MediaBundle\Provider\YouTubeProvider;
use Sonata\MediaBundle\Thumbnail\FileThumbnail;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.pool', Pool::class)
            ->public()
            ->args([''])

        ->alias(Pool::class, 'sonata.media.pool')

        ->set('sonata.media.thumbnail.format', FormatThumbnail::class)
            ->args(['jpg'])

        ->set('sonata.media.thumbnail.liip_imagine', LiipImagineThumbnail::class)
            ->args([
                new ReferenceConfigurator('liip_imagine.cache.manager'),
            ])

        ->set('sonata.media.thumbnail.file', FileThumbnail::class)
            ->args([
                new ReferenceConfigurator('assets.packages'),
            ])

        ->set('sonata.media.provider.image', ImageProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.image',
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_image.html.twig',
            ]])

        ->set('sonata.media.provider.file', FileProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.file',
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.thumbnail.file'),
                '',
                '',
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_file.html.twig',
            ]])

        ->set('sonata.media.provider.youtube', YouTubeProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.youtube',
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                new ReferenceConfigurator('sonata.media.http.client'),
                new ReferenceConfigurator('sonata.media.http.message_factory'),
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
                '',
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_youtube.html.twig',
            ]])

        ->set('sonata.media.provider.dailymotion', DailyMotionProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.dailymotion',
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                new ReferenceConfigurator('sonata.media.http.client'),
                new ReferenceConfigurator('sonata.media.http.message_factory'),
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_dailymotion.html.twig',
            ]])

        ->set('sonata.media.provider.vimeo', VimeoProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.vimeo',
                '',
                '',
                '',
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                new ReferenceConfigurator('sonata.media.http.client'),
                new ReferenceConfigurator('sonata.media.http.message_factory'),
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_vimeo.html.twig',
            ]]);
};
