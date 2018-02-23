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
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProxyMetadataBuilderTest extends TestCase
{
    public function testProxyAmazon(): void
    {
        $amazon = $this->createMock(AmazonMetadataBuilder::class);
        $amazon->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['key' => 'amazon']));

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->will($this->returnValue(['key' => 'noop']));

        //adapter cannot be mocked
        $amazonclient = S3Client::factory([
            'credentials' => [
                'key' => 'XXXXXXXXXXXX',
                'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            ],
            'region' => 'us-west-1',
        ]);
        $adapter = new AwsS3($amazonclient, '');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('sonata.media.provider.image'));

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainerMock([
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
            ->will($this->returnValue(['key' => 'amazon']));

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['key' => 'noop']));

        //adapter cannot be mocked
        $adapter = new Local('');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('sonata.media.provider.image'));

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainerMock([
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
            ->will($this->returnValue(['key' => 'amazon']));

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->will($this->returnValue(['key' => 'noop']));

        //adapter cannot be mocked
        $adapter = new Local('');

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('wrongprovider'));

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainerMock([
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
            ->will($this->returnValue(['key' => 'amazon']));

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->never())
            ->method('get')
            ->will($this->returnValue(['key' => 'noop']));

        //adapter cannot be mocked
        $amazonclient = S3Client::factory([
            'credentials' => [
                'key' => 'XXXXXXXXXXXX',
                'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            ],
            'region' => 'us-west-1',
        ]);
        $adapter1 = new AwsS3($amazonclient, '');
        $adapter2 = new Local('');
        $adapter = new Replicate($adapter1, $adapter2);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('sonata.media.provider.image'));

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainerMock([
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
            ->will($this->returnValue(['key' => 'amazon']));

        $noop = $this->createMock(NoopMetadataBuilder::class);
        $noop->expects($this->once())
            ->method('get')
            ->will($this->returnValue(['key' => 'noop']));

        //adapter cannot be mocked
        $adapter1 = new Local('');
        $adapter2 = new Local('');
        $adapter = new Replicate($adapter1, $adapter2);

        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->any())->method('getAdapter')->will($this->returnValue($adapter));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('sonata.media.provider.image'));

        $filename = '/test/folder/testfile.png';

        $container = $this->getContainerMock([
            'sonata.media.metadata.noop' => $noop,
            'sonata.media.metadata.amazon' => $amazon,
            'sonata.media.provider.image' => $provider,
        ]);

        $proxymetadatabuilder = new ProxyMetadataBuilder($container);

        $this->assertSame(['key' => 'noop'], $proxymetadatabuilder->get($media, $filename));
    }

    /**
     * Return a mock object for the DI ContainerInterface.
     *
     * @param array $services A key-value list of services the container contains
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainerMock(array $services)
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($service) use ($services) {
                return $services[$service];
            }))
        ;
        $container
            ->expects($this->any())
            ->method('has')
            ->will($this->returnCallback(function ($service) use ($services) {
                if (isset($services[$service])) {
                    return true;
                }

                return false;
            }))
        ;

        return $container;
    }
}
