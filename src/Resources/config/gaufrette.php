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

use Aws\S3\S3Client;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Adapter\Ftp;
use Gaufrette\Adapter\OpenCloud;
use Gaufrette\Filesystem;
use OpenCloud\ObjectSource\Service;
use OpenCloud\OpenStack;
use OpenCloud\Rackspace;
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
    $services = $containerConfigurator->services();

    $services->set('sonata.media.adapter.filesystem.local', Local::class);
    $services->set('sonata.media.adapter.filesystem.ftp', Ftp::class);

    $services->set('sonata.media.adapter.service.s3', S3Client::class)
        ->args([[]]);

    $services->set('sonata.media.adapter.filesystem.s3', AwsS3::class)
        ->args(['', '', '']);

    $services->set('sonata.media.adapter.filesystem.replicate', Replicate::class)
        ->args([
            '',
            '',
            // NEXT_MAJOR: make symfony/monolog-bundle a require dependency and remove nullOnInvalid
            (new ReferenceConfigurator('logger'))->nullOnInvalid(),
        ]);

    $services->set('sonata.media.adapter.filesystem.opencloud', OpenCloud::class)
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.opencloud.objectstore'),
            '',
            '',
        ]);

    $services->set('sonata.media.adapter.filesystem.opencloud.connection.openstack', Openstack::class)
        ->args(['', '']);

    $services->set('sonata.media.adapter.filesystem.opencloud.connection.rackspace', Rackspace::class)
        ->args(['', '']);

    $services->set('sonata.media.adapter.filesystem.opencloud.objectstore', Service::class)
        ->args(['', '']);

    $services->set('sonata.media.filesystem.ftp', Filesystem::class)
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.ftp'),
        ]);

    $services->set('sonata.media.filesystem.local', Filesystem::class)
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.local'),
        ]);

    $services->set('sonata.media.filesystem.s3', Filesystem::class)
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.s3'),
        ]);

    $services->set('sonata.media.filesystem.replicate', Filesystem::class)
        ->args([
            new ReferenceConfigurator('sonata.media.adapter.filesystem.replicate'),
        ]);

    $services->set('sonata.media.metadata.proxy', ProxyMetadataBuilder::class)
        ->public()
        ->args([
            new ReferenceConfigurator('service_container'),
        ]);

    $services->set('sonata.media.metadata.amazon', AmazonMetadataBuilder::class)
        ->public()
        ->args([[]]);

    $services->set('sonata.media.metadata.noop', NoopMetadataBuilder::class)
        ->public();
};
