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
    $services = $containerConfigurator->services();

    $services->set(AddMassMediaCommand::class, AddMassMediaCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(AddMediaCommand::class, AddMediaCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(CleanMediaCommand::class, CleanMediaCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.local'),
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(FixMediaContextCommand::class, FixMediaContextCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            (new ReferenceConfigurator('sonata.media.manager.category'))->nullOnInvalid(),
            (new ReferenceConfigurator('sonata.classification.manager.context'))->nullOnInvalid(),
        ]);

    $services->set(MigrateToJsonTypeCommand::class, MigrateToJsonTypeCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            (new ReferenceConfigurator('doctrine.orm.entity_manager'))->nullOnInvalid(),
        ]);

    $services->set(RefreshMetadataCommand::class, RefreshMetadataCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(RemoveThumbsCommand::class, RemoveThumbsCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(SyncThumbsCommand::class, SyncThumbsCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);

    $services->set(UpdateCdnStatusCommand::class, UpdateCdnStatusCommand::class)
        ->public()
        ->tag('console.command')
        ->args([
            new ReferenceConfigurator('sonata.media.pool'),
            new ReferenceConfigurator('sonata.media.manager.media'),
        ]);
};
