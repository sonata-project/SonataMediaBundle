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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Filesystem\Replicate;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Metadata\ProxyMetadataBuilder;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;

final class ProxyMetadataBuilderTest extends TestCase
{
    /**
     * @var Stub&Filesystem
     */
    private Stub $filesystem;

    /**
     * @var MockObject&MetadataBuilderInterface
     */
    private MockObject $noop;

    /**
     * @var MockObject&MetadataBuilderInterface
     */
    private MockObject $amazon;

    private ProxyMetadataBuilder $proxyMetadataBuilder;

    protected function setUp(): void
    {
        $this->filesystem = $this->createStub(Filesystem::class);
        $this->noop = $this->createMock(MetadataBuilderInterface::class);
        $this->amazon = $this->createMock(MetadataBuilderInterface::class);

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('getFilesystem')->willReturn($this->filesystem);

        $pool = new Pool('default_context');
        $pool->addProvider('sonata.media.provider.image', $provider);

        $this->proxyMetadataBuilder = new ProxyMetadataBuilder($pool, $this->noop, $this->amazon);
    }

    public function testProxyAmazon(): void
    {
        $amazonclient = new S3Client([
            'credentials' => [
                'key' => 'XXXXXXXXXXXX',
                'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            ],
            'region' => 'us-west-1',
            'version' => '2006-03-01',
            'suppress_php_deprecation_warning' => true,
        ]);
        $adapter = new AwsS3($amazonclient, '');
        $media = new Media();
        $media->setProviderName('sonata.media.provider.image');

        $this->filesystem->method('getAdapter')->willReturn($adapter);
        $this->amazon->expects(static::once())
            ->method('get')
            ->willReturn(['key' => 'amazon']);
        $this->noop->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        static::assertSame(
            ['key' => 'amazon'],
            $this->proxyMetadataBuilder->get($media, '/test/folder/testfile.png')
        );
    }

    public function testProxyLocal(): void
    {
        $adapter = new Local('');
        $media = new Media();
        $media->setProviderName('sonata.media.provider.image');

        $this->filesystem->method('getAdapter')->willReturn($adapter);
        $this->amazon->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);
        $this->noop->expects(static::once())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        static::assertSame(
            ['key' => 'noop'],
            $this->proxyMetadataBuilder->get($media, '/test/folder/testfile.png')
        );
    }

    public function testProxyNoProvider(): void
    {
        $adapter = new Local('');
        $media = new Media();

        $this->filesystem->method('getAdapter')->willReturn($adapter);
        $this->amazon->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);
        $this->noop->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        static::assertSame(
            [],
            $this->proxyMetadataBuilder->get($media, '/test/folder/testfile.png')
        );
    }

    public function testProxyReplicateWithAmazon(): void
    {
        $amazonclient = new S3Client([
            'credentials' => [
                'key' => 'XXXXXXXXXXXX',
                'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
            ],
            'region' => 'us-west-1',
            'version' => '2006-03-01',
            'suppress_php_deprecation_warning' => true,
        ]);
        $adapter = new Replicate(
            new AwsS3($amazonclient, ''),
            new Local('')
        );
        $media = new Media();
        $media->setProviderName('sonata.media.provider.image');

        $this->filesystem->method('getAdapter')->willReturn($adapter);
        $this->amazon->expects(static::once())
            ->method('get')
            ->willReturn(['key' => 'amazon']);
        $this->noop->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        static::assertSame(
            ['key' => 'amazon'],
            $this->proxyMetadataBuilder->get($media, '/test/folder/testfile.png')
        );
    }

    public function testProxyReplicateWithoutAmazon(): void
    {
        $adapter = new Replicate(new Local(''), new Local(''));
        $media = new Media();
        $media->setProviderName('sonata.media.provider.image');

        $this->filesystem->method('getAdapter')->willReturn($adapter);
        $this->amazon->expects(static::never())
            ->method('get')
            ->willReturn(['key' => 'amazon']);
        $this->noop->expects(static::once())
            ->method('get')
            ->willReturn(['key' => 'noop']);

        static::assertSame(
            ['key' => 'noop'],
            $this->proxyMetadataBuilder->get($media, '/test/folder/testfile.png')
        );
    }
}
