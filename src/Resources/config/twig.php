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

use Sonata\MediaBundle\Twig\Extension\FormatterMediaExtension;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Twig\GlobalVariables;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.twig.extension', MediaExtension::class)
            ->tag('twig.extension')

        ->set('sonata.media.twig.runtime', MediaRuntime::class)
            ->tag('twig.runtime')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
                new ReferenceConfigurator('twig'),
            ])

        ->set('sonata.media.twig.global', GlobalVariables::class)
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
            ])

        ->set('sonata.media.formatter.twig', FormatterMediaExtension::class);
};
