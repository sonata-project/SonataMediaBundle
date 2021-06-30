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

use Sonata\MediaBundle\Command\AddMassMediaCommand;
use Sonata\MediaBundle\Command\AddMediaCommand;
use Sonata\MediaBundle\Command\CleanMediaCommand;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Command\MigrateToJsonTypeCommand;
use Sonata\MediaBundle\Command\RefreshMetadataCommand;
use Sonata\MediaBundle\Command\RemoveThumbsCommand;
use Sonata\MediaBundle\Command\SyncThumbsCommand;
use Sonata\MediaBundle\Command\UpdateCdnStatusCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    $containerConfigurator->services()

        ->set('sonata.media.command.add_mass_media', AddMassMediaCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.add_media', AddMediaCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.clean_media', CleanMediaCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.filesystem.local'),
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.fix_media_context', FixMediaContextCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
                (new ReferenceConfigurator('sonata.media.manager.context'))->nullOnInvalid(),
            ])

        ->set('sonata.media.command.migrate_to_json_type', MigrateToJsonTypeCommand::class)
            ->tag('console.command')
            ->args([
                (new ReferenceConfigurator('doctrine.orm.entity_manager'))->nullOnInvalid(),
            ])

        ->set('sonata.media.command.refresh_metadata', RefreshMetadataCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.remove_thumbs', RemoveThumbsCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.sync_thumbs', SyncThumbsCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.update_cdn_status', UpdateCdnStatusCommand::class)
            ->tag('console.command')
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                new ReferenceConfigurator('sonata.media.manager.media'),
            ]);
};
