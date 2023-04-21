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

use Sonata\MediaBundle\Block\FeatureMediaBlockService;
use Sonata\MediaBundle\Block\GalleryBlockService;
use Sonata\MediaBundle\Block\GalleryListBlockService;
use Sonata\MediaBundle\Block\MediaBlockService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.block.media', MediaBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.media.pool'),
                service('sonata.media.admin.media')->nullOnInvalid(),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.block.feature_media', FeatureMediaBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.media.pool'),
                service('sonata.media.admin.media')->nullOnInvalid(),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.block.gallery', GalleryBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.media.pool'),
                service('sonata.media.admin.gallery')->nullOnInvalid(),
                service('sonata.media.manager.gallery'),
            ])

        ->set('sonata.media.block.gallery_list', GalleryListBlockService::class)
            ->tag('sonata.block')
            ->args([
                service('twig'),
                service('sonata.media.manager.gallery'),
                service('sonata.media.pool'),
            ]);
};
