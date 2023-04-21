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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Sonata\MediaBundle\Provider\DailyMotionProvider;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Provider\VimeoProvider;
use Sonata\MediaBundle\Provider\YouTubeProvider;
use Sonata\MediaBundle\Thumbnail\FileThumbnail;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.pool', Pool::class)
            ->public()
            ->args([abstract_arg('default context')])

        ->alias(Pool::class, 'sonata.media.pool')

        ->set('sonata.media.thumbnail.format', FormatThumbnail::class)
            ->args(['jpg'])

        ->set('sonata.media.thumbnail.liip_imagine', LiipImagineThumbnail::class)
            ->args([
                service('liip_imagine.cache.manager'),
            ])

        ->set('sonata.media.thumbnail.file', FileThumbnail::class)
            ->args([
                service('assets.packages'),
            ])

        ->set('sonata.media.provider.image', ImageProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.image',
                abstract_arg('filesystem'),
                abstract_arg('cdn'),
                abstract_arg('path generator'),
                service('sonata.media.thumbnail.format'),
                abstract_arg('allowed extensions'),
                abstract_arg('allowed mime types'),
                abstract_arg('imagine adapter'),
                service('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_image.html.twig',
            ]])

        ->set('sonata.media.provider.file', FileProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.file',
                abstract_arg('filesystem'),
                abstract_arg('cdn'),
                abstract_arg('path generator'),
                service('sonata.media.thumbnail.file'),
                abstract_arg('allowed extensions'),
                abstract_arg('allowed mime types'),
                service('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_file.html.twig',
            ]])

        ->set('sonata.media.provider.youtube', YouTubeProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.youtube',
                abstract_arg('filesystem'),
                abstract_arg('cdn'),
                abstract_arg('path generator'),
                service('sonata.media.thumbnail.format'),
                service('sonata.media.http.client'),
                service('sonata.media.http.message_factory'),
                service('sonata.media.metadata.proxy'),
                abstract_arg('is html5 player enabled'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_youtube.html.twig',
            ]])

        ->set('sonata.media.provider.dailymotion', DailyMotionProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.dailymotion',
                abstract_arg('filesystem'),
                abstract_arg('cdn'),
                abstract_arg('path generator'),
                service('sonata.media.thumbnail.format'),
                service('sonata.media.http.client'),
                service('sonata.media.http.message_factory'),
                service('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_dailymotion.html.twig',
            ]])

        ->set('sonata.media.provider.vimeo', VimeoProvider::class)
            ->tag('sonata.media.provider')
            ->args([
                'sonata.media.provider.vimeo',
                abstract_arg('filesystem'),
                abstract_arg('cdn'),
                abstract_arg('path generator'),
                service('sonata.media.thumbnail.format'),
                service('sonata.media.http.client'),
                service('sonata.media.http.message_factory'),
                service('sonata.media.metadata.proxy'),
            ])
            ->call('setTemplates', [[
                'helper_thumbnail' => '@SonataMedia/Provider/thumbnail.html.twig',
                'helper_view' => '@SonataMedia/Provider/view_vimeo.html.twig',
            ]]);
};
