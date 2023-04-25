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

use Sonata\MediaBundle\Messenger\GenerateThumbnailsHandler;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.messenger.generate_thumbnails', GenerateThumbnailsHandler::class)
            ->tag('messenger.message_handler')
            ->args([
                service('sonata.media.thumbnail.format'),
                service('sonata.media.manager.media'),
                service('sonata.media.pool'),
            ])

        ->set('sonata.media.thumbnail.messenger', MessengerThumbnail::class)
            ->tag('sonata.block')
            ->args([
                service('sonata.media.thumbnail.format'),
                service('sonata.media.messenger.generate_thumbnails_bus'),
            ]);
};
