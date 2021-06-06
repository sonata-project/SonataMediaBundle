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
use Sonata\MediaBundle\CDN\Fallback;
use Sonata\MediaBundle\CDN\PantherPortal;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Resizer\CropResizer;
use Sonata\MediaBundle\Resizer\SimpleResizer;
use Sonata\MediaBundle\Resizer\SquareResizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('sonata.media.resizer.crop.class', CropResizer::class);
    $parameters->set('sonata.media.resizer.simple.class', SimpleResizer::class);
    $parameters->set('sonata.media.resizer.square.class', SquareResizer::class);
    $parameters->set('sonata.media.adapter.image.gd.class', GdImagine::class);
    $parameters->set('sonata.media.adapter.image.imagick.class', ImagickImagine::class);
    $parameters->set('sonata.media.adapter.image.gmagick.class', GmagickImagine::class);

    $services = $containerConfigurator->services();

    $services->set('sonata.media.cdn.server', Server::class)
        ->args(['']);

    $services->set('sonata.media.cdn.panther', PantherPortal::class)
        ->args(['', '', '', '']);

    $services->set('sonata.media.cdn.cloudfront.client', CloudFrontClient::class)
        ->args([[]]);

    // The class for "sonata.media.cdn.cloudfront" service is set dynamically at `SonataMediaExtension`
    $services->set('sonata.media.cdn.cloudfront')
        ->args(['', '', '']);

    $services->set('sonata.media.cdn.fallback', Fallback::class)
        ->args(['', '']);
};
