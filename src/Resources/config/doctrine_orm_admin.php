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

use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Admin\GalleryItemAdmin;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->alias('sonata.media.admin.media.manager', 'sonata.admin.manager.orm')

        ->set('sonata.media.admin.media', MediaAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => '%sonata.media.media.class%',
                'controller' => 'sonata.media.controller.media.admin',
                'manager_type' => 'orm',
                'group' => 'sonata_media',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'media',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
                (new ReferenceConfigurator('sonata.media.manager.context'))->nullOnInvalid(),
            ])
            ->call('setModelManager', [new ReferenceConfigurator('sonata.media.admin.media.manager')])
            ->call('setTemplates', [[
                'inner_list_row' => '@SonataMedia/MediaAdmin/inner_row_media.html.twig',
                'outer_list_rows_mosaic' => '@SonataMedia/MediaAdmin/list_outer_rows_mosaic.html.twig',
                'base_list_field' => '@SonataAdmin/CRUD/base_list_flat_field.html.twig',
                'list' => '@SonataMedia/MediaAdmin/list.html.twig',
                'edit' => '@SonataMedia/MediaAdmin/edit.html.twig',
            ]])

        ->set('sonata.media.admin.gallery', GalleryAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => '%sonata.media.gallery.class%',
                'controller' => 'sonata.media.controller.gallery.admin',
                'manager_type' => 'orm',
                'group' => 'sonata_media',
                'label' => 'gallery',
                'translation_domain' => 'SonataMediaBundle',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
            ])
            ->call('setTemplates', [[
                'list' => '@SonataMedia/GalleryAdmin/list.html.twig',
            ]])

        ->set('sonata.media.admin.gallery_item', GalleryItemAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => '%sonata.media.gallery_item.class%',
                'controller' => '%sonata.admin.configuration.default_controller%',
                'manager_type' => 'orm',
                'show_in_dashboard' => false,
                'group' => 'sonata_media',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'gallery_item',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ]);
};
