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

use Sonata\MediaBundle\Form\Type\MediaType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.media.form.type.media', MediaType::class)
        ->tag('form.type', ['alias' => 'sonata_media_type'])
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            '',
        ])
        // NEXT_MAJOR: make symfony/monolog-bundle a require dependency and remove nullOnInvalid
        ->call('setLogger', [(new ReferenceConfigurator('logger'))->nullOnInvalid()]);
};
