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

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Admin\GalleryItemAdmin;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin;
use Sonata\MediaBundle\Controller\GalleryAdminController;
use Sonata\MediaBundle\Controller\MediaAdminController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('sonata.media.admin.groupname', 'sonata_media');
    $parameters->set('sonata.media.admin.groupicon', '<i class=\'fa fa-image\'></i>');
    $parameters->set('sonata.media.admin.media.controller', MediaAdminController::class);
    $parameters->set('sonata.media.admin.media.translation_domain', 'SonataMediaBundle');
    $parameters->set('sonata.media.admin.gallery.controller', GalleryAdminController::class);
    $parameters->set('sonata.media.admin.gallery.translation_domain', '%sonata.media.admin.media.translation_domain%');
    $parameters->set('sonata.media.admin.gallery_item.controller', CRUDController::class);
    $parameters->set('sonata.media.admin.gallery_item.translation_domain', '%sonata.media.admin.media.translation_domain%');

    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $services = $containerConfigurator->services();

    $services->set('sonata.media.admin.media', MediaAdmin::class)
        ->public()
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'group' => '%sonata.media.admin.groupname%',
            'label_catalogue' => '%sonata.media.admin.media.translation_domain%',
            'label' => 'media',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'icon' => '%sonata.media.admin.groupicon%',
        ])
        ->args([
            '',
            '%sonata.media.admin.media.entity%',
            '%sonata.media.admin.media.controller%',
            new ReferenceConfigurator('sonata.media.pool'),
            (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
        ])
        ->call('setModelManager', [new ReferenceConfigurator('sonata.media.admin.media.manager')])
        ->call('setTranslationDomain', ['%sonata.media.admin.media.translation_domain%'])
        ->call('setTemplates', [[
            'inner_list_row' => '@SonataMedia/MediaAdmin/inner_row_media.html.twig',
            'outer_list_rows_mosaic' => '@SonataMedia/MediaAdmin/list_outer_rows_mosaic.html.twig',
            'base_list_field' => '@SonataAdmin/CRUD/base_list_flat_field.html.twig',
            'list' => '@SonataMedia/MediaAdmin/list.html.twig',
            'edit' => '@SonataMedia/MediaAdmin/edit.html.twig',
        ]]);

    $services->set('sonata.media.admin.gallery', GalleryAdmin::class)
        ->public()
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'group' => '%sonata.media.admin.groupname%',
            'label' => 'gallery',
            'label_catalogue' => '%sonata.media.admin.gallery.translation_domain%',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'icon' => '%sonata.media.admin.groupicon%',
        ])
        ->args([
            '',
            '%sonata.media.admin.gallery.entity%',
            '%sonata.media.admin.gallery.controller%',
            new ReferenceConfigurator('sonata.media.pool'),
        ])
        ->call('setTranslationDomain', ['%sonata.media.admin.gallery.translation_domain%'])
        ->call('setTemplates', [[
            'list' => '@SonataMedia/GalleryAdmin/list.html.twig',
        ]]);

    $services->set('sonata.media.admin.gallery_item', GalleryItemAdmin::class)
        ->public()
        ->tag('sonata.admin', [
            'manager_type' => 'orm',
            'show_in_dashboard' => false,
            'group' => '%sonata.media.admin.groupname%',
            'label_catalogue' => '%sonata.media.admin.gallery_item.translation_domain%',
            'label' => 'gallery_item',
            'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
            'icon' => '%sonata.media.admin.groupicon%',
        ])
        ->args([
            '',
            '%sonata.media.admin.gallery_item.entity%',
            '%sonata.media.admin.gallery_item.controller%',
        ])
        ->call('setTranslationDomain', ['%sonata.media.admin.gallery_item.translation_domain%']);

    $services->alias('sonata.media.admin.media.manager', 'sonata.admin.manager.orm')
        ->public();
};
