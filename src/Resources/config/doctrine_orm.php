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

use Sonata\MediaBundle\Entity\GalleryManager;
use Sonata\MediaBundle\Entity\MediaManager;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Listener\ORM\MediaEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.media.manager.media', MediaManager::class)
            ->args([
                '%sonata.media.media.class%',
                new ReferenceConfigurator('doctrine'),
            ])

        ->set('sonata.media.manager.gallery', GalleryManager::class)
            ->args([
                '%sonata.media.gallery.class%',
                new ReferenceConfigurator('doctrine'),
            ])

        ->set('sonata.media.generator.default', IdGenerator::class)

        ->set('sonata.media.doctrine.event_subscriber', MediaEventSubscriber::class)
            ->tag('doctrine.event_subscriber')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
            ]);
};
