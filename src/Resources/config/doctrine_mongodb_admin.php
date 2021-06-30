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
use Sonata\MediaBundle\Admin\ODM\MediaAdmin;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->alias('sonata.media.admin.media.manager', 'sonata.admin.manager.doctrine_mongodb')

        ->set('sonata.media.admin.media', MediaAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'label_catalogue' => 'SonataMediaBundle',
                'label' => 'media',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                '',
                '%sonata.media.admin.media.entity%',
                'sonata.media.controller.media.admin',
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
            ])
            ->call('setModelManager', [new ReferenceConfigurator('sonata.media.admin.media.manager')])
            ->call('setTranslationDomain', ['SonataMediaBundle'])
            ->call('setTemplates', [[
                'inner_list_row' => '@SonataMedia/MediaAdmin/inner_row_media.html.twig',
                'base_list_field' => '@SonataAdmin/CRUD/base_list_flat_field.html.twig',
                'list' => '@SonataMedia/MediaAdmin/list.html.twig',
                'edit' => '@SonataMedia/MediaAdmin/edit.html.twig',
            ]])

        ->set('sonata.media.admin.gallery', GalleryAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'label_catalogue' => 'SonataMediaBundle',
                'label' => 'gallery',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                '',
                '%sonata.media.admin.gallery.entity%',
                'sonata.media.controller.gallery.admin',
                new ReferenceConfigurator('sonata.media.pool'),
            ])
            ->call('setTranslationDomain', ['SonataMediaBundle'])
            ->call('setTemplates', [[
                'list' => '@SonataMedia/GalleryAdmin/list.html.twig',
            ]])

        ->set('sonata.media.admin.gallery_item', GalleryItemAdmin::class)
            ->tag('sonata.admin', [
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'label_catalogue' => 'SonataMediaBundle',
                'label' => 'gallery_item',
                'show_in_dashboard' => false,
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                '',
                '%sonata.media.admin.gallery_item.entity%',
                CRUDController::class,
            ])
            ->call('setTranslationDomain', ['SonataMediaBundle']);
};
