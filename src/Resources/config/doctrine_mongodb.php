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

use Sonata\MediaBundle\Document\GalleryManager;
use Sonata\MediaBundle\Document\MediaManager;
use Sonata\MediaBundle\Generator\UuidGenerator;
use Sonata\MediaBundle\Listener\ODM\MediaEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $services = $containerConfigurator->services();

    $services->alias('sonata.media.document_manager', 'doctrine_mongodb.odm.document_manager');

    $services->set('sonata.media.manager.media', MediaManager::class)
        ->public()
        ->args([
            '%sonata.media.media.class%',
            new ReferenceConfigurator('doctrine_mongodb'),
        ]);

    $services->set('sonata.media.manager.gallery', GalleryManager::class)
        ->public()
        ->args([
            '%sonata.media.gallery.class%',
            new ReferenceConfigurator('doctrine_mongodb'),
        ]);

    $services->set('sonata.media.generator.default', UuidGenerator::class);

    $services->set('sonata.media.doctrine.event_subscriber', MediaEventSubscriber::class)
        ->tag('doctrine_mongodb.odm.event_subscriber')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
        ]);
};
