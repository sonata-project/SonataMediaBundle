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

namespace Sonata\MediaBundle\Tests\Provider;

use Gaufrette\Adapter;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Tests\App\Provider\TestProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;

class BaseProviderTest extends AbstractProviderTest
{
    public function getProvider(): TestProvider
    {
        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setConstructorArgs([$adapter])
            ->getMock();

        $filesystem->method('get')->willReturn(new File('my_file.txt', $filesystem));

        $cdn = $this->createStub(CDNInterface::class);
        $cdn->method('flushPaths')->willReturn((string) mt_rand());
        $cdn->method('getFlushStatus')
            ->will($this->onConsecutiveCalls(
                CDNInterface::STATUS_OK,
                CDNInterface::STATUS_TO_FLUSH,
                CDNInterface::STATUS_WAITING,
                CDNInterface::STATUS_OK
            ));
        $cdn->method('getPath')->willReturnCallback(static function (string $path, bool $isFlushable): string {
            return '/uploads/media/'.$path;
        });

        $generator = new IdGenerator();

        $thumbnail = $this->createStub(ThumbnailInterface::class);

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail);
        $this->assertInstanceOf(BaseProvider::class, $provider);

        return $provider;
    }

    public function testBaseProvider(): void
    {
        $provider = $this->getProvider();
        $provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        $this->assertIsArray($provider->getTemplates());
        $this->assertSame('edit.twig', $provider->getTemplate('edit'));

        $this->assertInstanceOf(CDNInterface::class, $provider->getCdn());

        $provider->addFormat('small', []);

        $this->assertIsArray($provider->getFormat('small'));

        $media = new Media();
        $media->setContext('test');

        $this->assertSame('admin', $provider->getFormatName($media, 'admin'));
        $this->assertSame('reference', $provider->getFormatName($media, 'reference'));
        $this->assertSame('test_small', $provider->getFormatName($media, 'small'));
        $this->assertSame('test_small', $provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath(): void
    {
        $provider = $this->getProvider();
        $this->assertSame('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testFlushCdn(): void
    {
        $provider = $this->getProvider();
        $provider->addFormat('test', []);

        $media = new Media();
        $media->setId('42');
        $media->setCdnIsFlushable(true);

        $media->setContext('test');
        $this->assertNull($media->getCdnFlushIdentifier());
        $this->assertNull($media->getCdnStatus());
        $provider->flushCdn($media);
        $this->assertTrue($media->getCdnIsFlushable());
        $this->assertNotNull($media->getCdnFlushIdentifier());
        $this->assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());

        $media->setContext('other');
        $provider->flushCdn($media);
        $this->assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        $this->assertNull($media->getCdnFlushIdentifier());

        $media->setContext('test');
        $provider->flushCdn($media);
        $this->assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        $this->assertNotNull($media->getCdnFlushIdentifier());

        $media->setContext('other');
        $provider->flushCdn($media);
        $this->assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        $this->assertNotNull($media->getCdnFlushIdentifier());
        $provider->flushCdn($media);
        $this->assertSame(CDNInterface::STATUS_WAITING, $media->getCdnStatus());
        $this->assertNotNull($media->getCdnFlushIdentifier());
        $provider->flushCdn($media);
        $this->assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        $this->assertNull($media->getCdnFlushIdentifier());
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        $this->assertSame('test', $provider->getProviderMetadata()->getTitle());
        $this->assertSame('test.description', $provider->getProviderMetadata()->getDescription());
        $this->assertNotNull($provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-file', $provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }

    public function testPostRemove(): void
    {
        $reflect = new \ReflectionClass(BaseProvider::class);
        $prop = $reflect->getProperty('clones');
        $prop->setAccessible(true);

        $provider = $this->getProvider();
        $media = new Media();
        $media->setId(1399);
        $media->setProviderReference('1f981a048e7d8b671415d17e9633abc0059df394.png');
        $hash = spl_object_hash($media);

        $provider->preRemove($media);

        $this->assertArrayHasKey($hash, $prop->getValue($provider));

        $media->setId(null); // Emulate an object detached from the EntityManager.
        $provider->postRemove($media);

        $this->assertArrayNotHasKey($hash, $prop->getValue($provider));
        $this->assertSame('/0001/02/1f981a048e7d8b671415d17e9633abc0059df394.png', $provider->prevReferenceImage);

        $prop->setAccessible(false);
    }
}
