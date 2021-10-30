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

use Aws\CloudFront\CloudFrontClient;
use Imagine\Gd\Imagine as GdImagine;
use Imagine\Gmagick\Imagine as GmagickImagine;
use Imagine\Imagick\Imagine as ImagickImagine;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;
use Sonata\MediaBundle\CDN\Fallback;
use Sonata\MediaBundle\CDN\PantherPortal;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Resizer\CropResizer;
use Sonata\MediaBundle\Resizer\SimpleResizer;
use Sonata\MediaBundle\Resizer\SquareResizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.media.adapter.image.gd', GdImagine::class)

        ->set('sonata.media.adapter.image.imagick', ImagickImagine::class)

        ->set('sonata.media.adapter.image.gmagick', GmagickImagine::class)

        ->set('sonata.media.resizer.crop', CropResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.image.default'),
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.resizer.simple', SimpleResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.image.default'),
                '%sonata.media.resizer.simple.adapter.mode%',
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.resizer.square', SquareResizer::class)
            ->tag('sonata.media.resizer')
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.image.default'),
                '%sonata.media.resizer.square.adapter.mode%',
                new ReferenceConfigurator('sonata.media.metadata.proxy'),
            ])

        ->set('sonata.media.cdn.server', Server::class)
            ->args([''])

        ->set('sonata.media.cdn.panther', PantherPortal::class)
            ->args(['', '', '', ''])

        ->set('sonata.media.cdn.cloudfront.client', CloudFrontClient::class)
            ->args([[]])

        ->set('sonata.media.cdn.cloudfront', CloudFrontVersion3::class)
            ->args(['', '', ''])

        ->set('sonata.media.cdn.fallback', Fallback::class)
            ->args(['', '']);
};
