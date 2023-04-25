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

use AsyncAws\SimpleS3\SimpleS3Client;
use Aws\S3\S3Client;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Adapter\Ftp;
use Gaufrette\Filesystem;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Filesystem\Replicate;
use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Sonata\MediaBundle\Metadata\NoopMetadataBuilder;
use Sonata\MediaBundle\Metadata\ProxyMetadataBuilder;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->services()

        ->set('sonata.media.adapter.filesystem.local', Local::class)
        ->set('sonata.media.adapter.filesystem.ftp', Ftp::class)

        ->set('sonata.media.adapter.filesystem.s3', AwsS3::class)
            ->args([
                abstract_arg('s3 client'),
                abstract_arg('bucket'),
                abstract_arg('options'),
            ])

        ->set('sonata.media.adapter.filesystem.replicate', Replicate::class)
            ->args([
                abstract_arg('primary adapter'),
                abstract_arg('secondary adapter'),
                service('logger')->nullOnInvalid(),
            ])

        ->set('sonata.media.filesystem.local', Filesystem::class)
            ->args([
                service('sonata.media.adapter.filesystem.local'),
            ])

        ->set('sonata.media.filesystem.s3', Filesystem::class)
            ->args([
                service('sonata.media.adapter.filesystem.s3'),
            ])

        ->set('sonata.media.filesystem.ftp', Filesystem::class)
            ->args([
                service('sonata.media.adapter.filesystem.ftp'),
            ])

        ->set('sonata.media.filesystem.replicate', Filesystem::class)
            ->args([
                service('sonata.media.adapter.filesystem.replicate'),
            ])

        ->set('sonata.media.metadata.proxy', ProxyMetadataBuilder::class)
            ->args([
                service('sonata.media.pool'),
                service('sonata.media.metadata.noop')->nullOnInvalid(),
                service('sonata.media.metadata.amazon')->nullOnInvalid(),
            ])

        ->set('sonata.media.metadata.amazon', AmazonMetadataBuilder::class)
            ->args([abstract_arg('settings')])

        ->set('sonata.media.metadata.noop', NoopMetadataBuilder::class);

    if (class_exists(S3Client::class)) {
        $containerConfigurator->services()
            ->set('sonata.media.adapter.service.s3', S3Client::class)
            ->args([abstract_arg('settings')]);
    }

    if (class_exists(SimpleS3Client::class)) {
        $containerConfigurator->services()
            ->set('sonata.media.adapter.service.s3.async', SimpleS3Client::class)
            ->args([abstract_arg('settings')]);
    }
};
