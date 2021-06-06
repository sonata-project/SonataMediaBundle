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

use Sonata\MediaBundle\Consumer\CreateThumbnailConsumer;
use Sonata\MediaBundle\Thumbnail\ConsumerThumbnail;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.media.notification.create_thumbnail', CreateThumbnailConsumer::class)
        ->tag('sonata.notification.consumer', ['type' => 'sonata.media.create_thumbnail'])
        ->args([
            new ReferenceConfigurator('sonata.media.manager.media'),
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('service_container'),
        ]);

    $services->set('sonata.media.thumbnail.consumer.format', ConsumerThumbnail::class)
        ->args([
            'sonata.media.thumbnail.format',
            new ReferenceConfigurator('sonata.media.thumbnail.format'),
            new ReferenceConfigurator('sonata.notification.backend'),
            new ReferenceConfigurator('event_dispatcher'),
        ]);
};
