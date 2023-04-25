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

use Sonata\MediaBundle\Entity\GalleryManager;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.manager.media', MediaManager::class)
            ->args([
                param('sonata.media.media.class'),
                service('doctrine'),
            ])

        ->set('sonata.media.manager.gallery', GalleryManager::class)
            ->args([
                param('sonata.media.gallery.class'),
                service('doctrine'),
            ])

        ->set('sonata.media.generator.default', IdGenerator::class)

        ->set('sonata.media.doctrine.event_subscriber', MediaEventSubscriber::class)
            ->tag('doctrine.event_subscriber')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.category')->nullOnInvalid(),
            ]);
};
