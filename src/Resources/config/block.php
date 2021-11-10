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

use Sonata\MediaBundle\Block\FeatureMediaBlockService;
use Sonata\MediaBundle\Block\GalleryBlockService;
use Sonata\MediaBundle\Block\GalleryListBlockService;
use Sonata\MediaBundle\Block\MediaBlockService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.block.media', MediaBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.admin.media'))->nullOnInvalid(),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.block.feature_media', FeatureMediaBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.admin.media'))->nullOnInvalid(),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.block.gallery', GalleryBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.admin.gallery'),
                new ReferenceConfigurator('sonata.media.manager.gallery'),
            ])

        ->set('sonata.media.block.gallery_list', GalleryListBlockService::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('twig'),
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.admin.gallery'),
                new ReferenceConfigurator('sonata.media.manager.gallery'),
            ]);
};
