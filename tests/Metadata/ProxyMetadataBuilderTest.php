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

namespace Sonata\MediaBundle\Tests\Metadata;

use Aws\S3\S3Client;
use Aws\Sdk;
use Gaufrette\Adapter\AwsS3;
use Gaufrette\Filesystem;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Filesystem\Replicate;
use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Sonata\MediaBundle\Metadata\NoopMetadataBuilder;
use Sonata\MediaBundle\Metadata\ProxyMetadataBuilder;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ProxyMetadataBuilderTest extends TestCase
{
    public function testProxyAmazon(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->once())
            ->method('get')
            ->willReturn(['key' => 'amazon']);

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        if (class_exists(Sdk::class)) {
            // AWS v3.x
            $amazonclient = new S3Client([
                'credentials' => [
                    'key' => 'XXXXXXXXXXXX',
                    'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                ],
                'region' => 'us-west-1',
                'version' => '2006-03-01',
            ]);
        } else {
            // AWS v2.x. Remove this condition when the support for this version is dropped.
            $amazonclient = S3Client::factory([
                'credentials' => [
                    'key' => 'XXXXXXXXXXXX',
                    'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                ],
                'region' => 'us-west-1',
            ]);
        }

        // adapter cannot be mocked
        $adapter = new AwsS3($amazonclient, '');

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getAdapter')->willReturn($adapter);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createStub(MediaInterface::class);
        $media
            ->method('getProviderName')
            ->willReturn('sonata.media.provider.image');

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainer([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame(['key' => 'amazon'], $proxymetadatabuilder->get($media, $filename));
    }

    public function testProxyLocal(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->once())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        //adapter cannot be mocked
        $adapter = new Local('');

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getAdapter')->willReturn($adapter);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createStub(MediaInterface::class);
        $media
            ->method('getProviderName')
            ->willReturn('sonata.media.provider.image');

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainer([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame(['key' => 'noop'], $proxymetadatabuilder->get($media, $filename));
    }

    public function testProxyNoProvider(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        // adapter cannot be mocked
        $adapter = new Local('');

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getAdapter')->willReturn($adapter);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createStub(MediaInterface::class);
        $media
            ->method('getProviderName')
            ->willReturn('wrongprovider');

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainer([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame([], $proxymetadatabuilder->get($media, $filename));
    }

    public function testProxyReplicateWithAmazon(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->once())
            ->method('get')
            ->willReturn(['key' => 'amazon']);

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        if (class_exists(Sdk::class)) {
            // AWS v3.x
            $amazonclient = new S3Client([
                'credentials' => [
                    'key' => 'XXXXXXXXXXXX',
                    'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                ],
                'region' => 'us-west-1',
                'version' => '2006-03-01',
            ]);
        } else {
            // AWS v2.x. Remove this condition when the support for this version is dropped.
            $amazonclient = S3Client::factory([
                'credentials' => [
                    'key' => 'XXXXXXXXXXXX',
                    'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
                ],
                'region' => 'us-west-1',
            ]);
        }

        // adapter cannot be mocked
        $adapter1 = new AwsS3($amazonclient, '');
        $adapter2 = new Local('');
        $adapter = new Replicate($adapter1, $adapter2);

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getAdapter')->willReturn($adapter);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createStub(MediaInterface::class);
        $media
            ->method('getProviderName')
            ->willReturn('sonata.media.provider.image');

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainer([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame(['key' => 'amazon'], $proxymetadatabuilder->get($media, $filename));
    }

    public function testProxyReplicateWithoutAmazon(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->once())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        // adapter cannot be mocked
        $adapter1 = new Local('');
        $adapter2 = new Local('');
        $adapter = new Replicate($adapter1, $adapter2);

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getAdapter')->willReturn($adapter);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createStub(MediaInterface::class);
        $media
            ->method('getProviderName')
            ->willReturn('sonata.media.provider.image');

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainer([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame(['key' => 'noop'], $proxymetadatabuilder->get($media, $filename));
    }

    private function getContainer(array $services): ContainerInterface
    {
        $container = new Container();

        foreach ($services as $serviceId => $service) {
            $container->set($serviceId, $service);
        }

        return $container;
    }
}
