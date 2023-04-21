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

use Sonata\MediaBundle\Command\AddMassMediaCommand;
use Sonata\MediaBundle\Command\AddMediaCommand;
use Sonata\MediaBundle\Command\CleanMediaCommand;
use Sonata\MediaBundle\Command\FixMediaContextCommand;
use Sonata\MediaBundle\Command\RefreshMetadataCommand;
use Sonata\MediaBundle\Command\RemoveThumbsCommand;
use Sonata\MediaBundle\Command\SyncThumbsCommand;
use Sonata\MediaBundle\Command\UpdateCdnStatusCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.command.add_mass_media', AddMassMediaCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.add_media', AddMediaCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.clean_media', CleanMediaCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.adapter.filesystem.local'),
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.fix_media_context', FixMediaContextCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.category')->nullOnInvalid(),
                service('sonata.media.manager.context')->nullOnInvalid(),
            ])

        ->set('sonata.media.command.refresh_metadata', RefreshMetadataCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.remove_thumbs', RemoveThumbsCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.sync_thumbs', SyncThumbsCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
            ])

        ->set('sonata.media.command.update_cdn_status', UpdateCdnStatusCommand::class)
            ->tag('console.command')
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.manager.media'),
            ]);
};
