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

use Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType;
use Sonata\MediaBundle\Form\Type\ApiGalleryItemType;
use Sonata\MediaBundle\Form\Type\ApiGalleryType;
use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $services = $containerConfigurator->services();

    $services->set('sonata.media.api.form.type.doctrine.media', ApiDoctrineMediaType::class)
        ->tag('form.type')
        ->args([
            new ReferenceConfigurator('jms_serializer.metadata_factory'),
            new ReferenceConfigurator('doctrine_mongodb'),
            'sonata_media_api_form_doctrine_media',
            '%sonata.media.admin.media.entity%',
            'sonata_api_write',
        ]);

    $services->set('sonata.media.api.form.type.media', ApiMediaType::class)
        ->tag('form.type')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            '%sonata.media.admin.media.entity%',
        ])
        // NEXT_MAJOR: make symfony/monolog-bundle a require dependency and remove nullOnInvalid
        ->call('setLogger', [(new ReferenceConfigurator('logger'))->nullOnInvalid()]);

    $services->set('sonata.media.api.form.type.gallery', ApiGalleryType::class)
        ->tag('form.type')
        ->args([
            new ReferenceConfigurator('jms_serializer.metadata_factory'),
            new ReferenceConfigurator('doctrine_mongodb'),
            'sonata_media_api_form_gallery',
            '%sonata.media.admin.gallery.entity%',
            'sonata_api_write',
        ]);

    $services->set('sonata.media.api.form.type.gallery_item', ApiGalleryItemType::class)
        ->tag('form.type')
        ->args([
            new ReferenceConfigurator('jms_serializer.metadata_factory'),
            new ReferenceConfigurator('doctrine_mongodb'),
            'sonata_media_api_form_gallery_item',
            '%sonata.media.admin.gallery_item.entity%',
            'sonata_api_write',
        ]);
};
