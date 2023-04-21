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

use Sonata\MediaBundle\Twig\Extension\FormatterMediaExtension;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Twig\GlobalVariables;
use Sonata\MediaBundle\Twig\MediaRuntime;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.twig.extension', MediaExtension::class)
            ->tag('twig.extension')

        ->set('sonata.media.twig.runtime', MediaRuntime::class)
            ->tag('twig.runtime')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
                service('twig'),
            ])

        ->set('sonata.media.twig.global', GlobalVariables::class)
            ->args([
                service('sonata.media.pool'),
            ])

        ->set('sonata.media.formatter.twig', FormatterMediaExtension::class);
};
