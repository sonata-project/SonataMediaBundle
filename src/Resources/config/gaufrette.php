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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ReferenceConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // Use "service" function for creating references to services when dropping support for Symfony 4.4
    // Use "param" function for creating references to parameters when dropping support for Symfony 5.1
    $containerConfigurator->services()

        ->set('sonata.media.adapter.filesystem.local', Local::class)
        ->set('sonata.media.adapter.filesystem.ftp', Ftp::class)

        ->set('sonata.media.adapter.service.s3', S3Client::class)
            ->args([[]])

        ->set('sonata.media.adapter.filesystem.s3', AwsS3::class)
            ->args(['', '', ''])

        ->set('sonata.media.adapter.filesystem.replicate', Replicate::class)
            ->args([
                '',
                '',
                (new ReferenceConfigurator('logger'))->nullOnInvalid(),
            ])

        ->set('sonata.media.filesystem.local', Filesystem::class)
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.filesystem.local'),
            ])

        ->set('sonata.media.filesystem.s3', Filesystem::class)
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.filesystem.s3'),
            ])

        ->set('sonata.media.filesystem.ftp', Filesystem::class)
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.filesystem.ftp'),
            ])

        ->set('sonata.media.filesystem.replicate', Filesystem::class)
            ->args([
                new ReferenceConfigurator('sonata.media.adapter.filesystem.replicate'),
            ])

        ->set('sonata.media.metadata.proxy', ProxyMetadataBuilder::class)
            ->args([
                new ReferenceConfigurator('sonata.media.pool'),
                (new ReferenceConfigurator('sonata.media.metadata.noop'))->nullOnInvalid(),
                (new ReferenceConfigurator('sonata.media.metadata.amazon'))->nullOnInvalid(),
            ])

        ->set('sonata.media.metadata.amazon', AmazonMetadataBuilder::class)
            ->args([[]])

        ->set('sonata.media.metadata.noop', NoopMetadataBuilder::class);

    if (class_exists(SimpleS3Client::class)) {
        $containerConfigurator->services()
            ->set('sonata.media.adapter.service.s3.async', SimpleS3Client::class)
            ->args([[]]);
    }
};
