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

use Doctrine\ODM\MongoDB\Events;
use Sonata\MediaBundle\Document\GalleryManager;
use Sonata\MediaBundle\Document\MediaManager;
use Sonata\MediaBundle\Generator\UuidGenerator;
use Sonata\MediaBundle\Listener\ODM\MediaEventSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->alias('sonata.media.document_manager', 'doctrine_mongodb.odm.document_manager')

        ->set('sonata.media.manager.media', MediaManager::class)
            ->args([
                param('sonata.media.media.class'),
                service('doctrine_mongodb'),
            ])

        ->set('sonata.media.manager.gallery', GalleryManager::class)
            ->args([
                param('sonata.media.gallery.class'),
                service('doctrine_mongodb'),
            ])

        ->set('sonata.media.generator.default', UuidGenerator::class)

        ->set('sonata.media.doctrine.event_subscriber', MediaEventSubscriber::class)
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::prePersist,
            ])
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::preUpdate,
            ])
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::preRemove,
            ])
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::postUpdate,
            ])
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::postRemove,
            ])
            ->tag('doctrine_mongodb.odm.event_listener', [
                'event' => Events::postPersist,
            ])
            ->args([
                service('sonata.media.pool'),
            ]);
};
