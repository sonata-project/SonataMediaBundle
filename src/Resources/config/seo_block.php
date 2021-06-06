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

use Sonata\MediaBundle\Block\Breadcrumb\GalleryIndexBreadcrumbBlockService;
use Sonata\MediaBundle\Block\Breadcrumb\GalleryViewBreadcrumbBlockService;
use Sonata\MediaBundle\Block\Breadcrumb\MediaViewBreadcrumbBlockService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $services = $containerConfigurator->services();

    $services->set('sonata.media.block.breadcrumb_view', GalleryViewBreadcrumbBlockService::class)
        ->tag('sonata.breadcrumb')
        ->tag('sonata.block', ['context' => 'breadcrumb'])
        ->args([
            new ReferenceConfigurator('twig'),
            new ReferenceConfigurator('knp_menu.menu_provider'),
            new ReferenceConfigurator('knp_menu.factory'),
        ]);

    $services->set('sonata.media.block.breadcrumb_index', GalleryIndexBreadcrumbBlockService::class)
        ->tag('sonata.breadcrumb')
        ->tag('sonata.block', ['context' => 'breadcrumb'])
        ->args([
            new ReferenceConfigurator('twig'),
            new ReferenceConfigurator('knp_menu.menu_provider'),
            new ReferenceConfigurator('knp_menu.factory'),
        ]);

    $services->set('sonata.media.block.breadcrumb_view_media', MediaViewBreadcrumbBlockService::class)
        ->tag('sonata.breadcrumb')
        ->tag('sonata.block', ['context' => 'breadcrumb'])
        ->args([
            new ReferenceConfigurator('twig'),
            new ReferenceConfigurator('knp_menu.menu_provider'),
            new ReferenceConfigurator('knp_menu.factory'),
        ]);
};
