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

use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Admin\GalleryItemAdmin;
use Sonata\MediaBundle\Admin\ODM\MediaAdmin;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->alias('sonata.media.admin.media.manager', 'sonata.admin.manager.doctrine_mongodb')

        ->set('sonata.media.admin.media', MediaAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.media.media.class'),
                'controller' => 'sonata.media.controller.media.admin',
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'media',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.category')->nullOnInvalid(),
                service('sonata.media.manager.context')->nullOnInvalid(),
            ])
            ->call('setModelManager', [service('sonata.media.admin.media.manager')])
            ->call('setTemplates', [[
                'inner_list_row' => '@SonataMedia/MediaAdmin/inner_row_media.html.twig',
                'base_list_field' => '@SonataAdmin/CRUD/base_list_flat_field.html.twig',
                'list' => '@SonataMedia/MediaAdmin/list.html.twig',
                'edit' => '@SonataMedia/MediaAdmin/edit.html.twig',
            ]])

        ->set('sonata.media.admin.gallery', GalleryAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.media.gallery.class'),
                'controller' => 'sonata.media.controller.gallery.admin',
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'gallery',
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ])
            ->args([
                service('sonata.media.pool'),
            ])
            ->call('setTemplates', [[
                'list' => '@SonataMedia/GalleryAdmin/list.html.twig',
            ]])

        ->set('sonata.media.admin.gallery_item', GalleryItemAdmin::class)
            ->tag('sonata.admin', [
                'model_class' => (string) param('sonata.media.gallery_item.class'),
                'controller' => (string) param('sonata.admin.configuration.default_controller'),
                'manager_type' => 'doctrine_mongodb',
                'group' => 'sonata_media',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'gallery_item',
                'show_in_dashboard' => false,
                'label_translator_strategy' => 'sonata.admin.label.strategy.underscore',
                'icon' => '<i class=\'fa fa-image\'></i>',
            ]);
};
