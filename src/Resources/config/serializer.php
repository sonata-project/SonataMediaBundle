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

use Sonata\MediaBundle\Serializer\GallerySerializerHandler;
use Sonata\MediaBundle\Serializer\MediaSerializerHandler;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.media.serializer.handler.media', MediaSerializerHandler::class)
        ->tag('jms_serializer.subscribing_handler')
        ->args([
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set('sonata.media.serializer.handler.gallery', GallerySerializerHandler::class)
        ->tag('jms_serializer.subscribing_handler')
        ->args([
            new ReferenceConfigurator('sonata.media.manager.gallery'),
        ]);
};
