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

use Sonata\MediaBundle\Messenger\GenerateThumbnailsHandler;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.messenger.generate_thumbnails', GenerateThumbnailsHandler::class)
            ->tag('messenger.message_handler')
            ->args([
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                new ReferenceConfigurator('sonata.media.manager.media'),
                new ReferenceConfigurator('sonata.media.pool'),
            ])

        ->set('sonata.media.thumbnail.messenger', MessengerThumbnail::class)
            ->tag('sonata.block')
            ->args([
                new ReferenceConfigurator('sonata.media.thumbnail.format'),
                new ReferenceConfigurator('sonata.media.messenger.generate_thumbnails_bus'),
            ]);
};
