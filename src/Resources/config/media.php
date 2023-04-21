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

use Aws\CloudFront\CloudFrontClient;
use Imagine\Gd\Imagine as GdImagine;
use Imagine\Gmagick\Imagine as GmagickImagine;
use Imagine\Imagick\Imagine as ImagickImagine;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;
use Sonata\MediaBundle\CDN\Fallback;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Resizer\CropResizer;
use Sonata\MediaBundle\Resizer\SimpleResizer;
use Sonata\MediaBundle\Resizer\SquareResizer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.adapter.image.gd', GdImagine::class)

        ->set('sonata.media.adapter.image.imagick', ImagickImagine::class)

        ->set('sonata.media.adapter.image.gmagick', GmagickImagine::class)

        ->set('sonata.media.resizer.crop', CropResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                service('sonata.media.adapter.image.default'),
                service('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.resizer.simple', SimpleResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                service('sonata.media.adapter.image.default'),
                param('sonata.media.resizer.simple.adapter.mode'),
                service('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.resizer.square', SquareResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                service('sonata.media.adapter.image.default'),
                param('sonata.media.resizer.square.adapter.mode'),
                service('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.cdn.server', Server::class)
            ->args([abstract_arg('path')])

        ->set('sonata.media.cdn.cloudfront.client', CloudFrontClient::class)
            ->args([abstract_arg('configuration')])

        ->set('sonata.media.cdn.cloudfront', CloudFrontVersion3::class)
            ->args([
                abstract_arg('cloudfront client'),
                abstract_arg('distribution id'),
                abstract_arg('path'),
            ])

        ->set('sonata.media.cdn.fallback', Fallback::class)
            ->args([
                abstract_arg('relative path'),
                abstract_arg('is flushable'),
            ]);
};
