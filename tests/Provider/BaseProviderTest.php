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
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Tests\App\Provider\TestProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;

/**
 * @phpstan-extends AbstractProviderTest<\Sonata\MediaBundle\Tests\App\Provider\TestProvider>
 */
class BaseProviderTest extends AbstractProviderTest
{
    public function getProvider(): MediaProviderInterface
    {
        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setConstructorArgs([$adapter])
            ->getMock();

        $filesystem->method('get')->willReturn(new File('my_file.txt', $filesystem));

        $cdn = $this->createStub(CDNInterface::class);
        $cdn->method('flushPaths')->willReturn((string) mt_rand());
        $cdn->method('getFlushStatus')
            ->will(self::onConsecutiveCalls(
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
        self::assertInstanceOf(BaseProvider::class, $provider);

        return $provider;
    }

    public function testBaseProvider(): void
    {
        $this->provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        self::assertIsArray($this->provider->getTemplates());
        self::assertSame('edit.twig', $this->provider->getTemplate('edit'));

        self::assertInstanceOf(CDNInterface::class, $this->provider->getCdn());

        $this->provider->addFormat('small', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => false,
            'resizer_options' => [],
        ]);

        self::assertIsArray($this->provider->getFormat('small'));

        $media = new Media();
        $media->setContext('test');

        self::assertSame('admin', $this->provider->getFormatName($media, 'admin'));
        self::assertSame('reference', $this->provider->getFormatName($media, 'reference'));
        self::assertSame('test_small', $this->provider->getFormatName($media, 'small'));
        self::assertSame('test_small', $this->provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath(): void
    {
        self::assertSame('/uploads/media/my_file.txt', $this->provider->getCdnPath('my_file.txt', false));
    }

    public function testFlushCdn(): void
    {
        $this->provider->addFormat('test', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => false,
            'resizer_options' => [],
        ]);

        $media = new Media();
        $media->setId('42');
        $media->setCdnIsFlushable(true);

        $media->setContext('test');
        self::assertNull($media->getCdnFlushIdentifier());
        self::assertNull($media->getCdnStatus());
        $this->provider->flushCdn($media);
        self::assertTrue($media->getCdnIsFlushable());
        self::assertNotNull($media->getCdnFlushIdentifier());
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());

        $media->setContext('other');
        $this->provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        self::assertNull($media->getCdnFlushIdentifier());

        $media->setContext('test');
        $this->provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());

        $media->setContext('other');
        $this->provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());
        $this->provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_WAITING, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());
        $this->provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        self::assertNull($media->getCdnFlushIdentifier());
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        self::assertSame('test', $provider->getProviderMetadata()->getTitle());
        self::assertSame('test.description', $provider->getProviderMetadata()->getDescription());
        self::assertNotNull($provider->getProviderMetadata()->getImage());
        self::assertSame('fa fa-file', $provider->getProviderMetadata()->getOption('class'));
        self::assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
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

        self::assertArrayHasKey($hash, $prop->getValue($provider));

        $media->setId(null); // Emulate an object detached from the EntityManager.
        $provider->postRemove($media);

        self::assertArrayNotHasKey($hash, $prop->getValue($provider));
        self::assertSame('/0001/02/1f981a048e7d8b671415d17e9633abc0059df394.png', $provider->prevReferenceImage);

        $prop->setAccessible(false);
    }
}
