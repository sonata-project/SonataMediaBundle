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
 * @phpstan-extends AbstractProviderTest<TestProvider>
 */
class BaseProviderTest extends AbstractProviderTest
{
    /**
     * @return TestProvider
     */
    public function getProvider(): MediaProviderInterface
    {
        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setConstructorArgs([$adapter])
            ->getMock();

        $filesystem->method('get')->willReturn(new File('my_file.txt', $filesystem));

        $cdn = $this->createStub(CDNInterface::class);
        $cdn->method('flushPaths')->willReturn((string) random_int(0, mt_getrandmax()));
        $cdn->method('getFlushStatus')
            ->will(static::onConsecutiveCalls(
                CDNInterface::STATUS_OK,
                CDNInterface::STATUS_TO_FLUSH,
                CDNInterface::STATUS_WAITING,
                CDNInterface::STATUS_OK
            ));
        $cdn->method('getPath')->willReturnCallback(static fn (string $path, bool $isFlushable): string => '/uploads/media/'.$path);

        $generator = new IdGenerator();

        $thumbnail = $this->createStub(ThumbnailInterface::class);

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail);
        static::assertInstanceOf(BaseProvider::class, $provider);

        return $provider;
    }

    public function testBaseProvider(): void
    {
        $this->provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        static::assertSame(['edit' => 'edit.twig'], $this->provider->getTemplates());
        static::assertSame('edit.twig', $this->provider->getTemplate('edit'));

        static::assertInstanceOf(CDNInterface::class, $this->provider->getCdn());

        $this->provider->addFormat('small', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        static::assertIsArray($this->provider->getFormat('small'));

        $media = new Media();
        $media->setContext('test');

        static::assertSame('admin', $this->provider->getFormatName($media, 'admin'));
        static::assertSame('reference', $this->provider->getFormatName($media, 'reference'));
        static::assertSame('test_small', $this->provider->getFormatName($media, 'small'));
        static::assertSame('test_small', $this->provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath(): void
    {
        static::assertSame('/uploads/media/my_file.txt', $this->provider->getCdnPath('my_file.txt', false));
    }

    public function testFlushCdn(): void
    {
        $this->provider->addFormat('test', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $media = new Media();
        $media->setId('42');
        $media->setCdnIsFlushable(true);

        $media->setContext('test');
        static::assertNull($media->getCdnFlushIdentifier());
        static::assertNull($media->getCdnStatus());
        $this->provider->flushCdn($media);
        static::assertTrue($media->getCdnIsFlushable());
        static::assertNotNull($media->getCdnFlushIdentifier());
        static::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());

        $media->setContext('other');
        $this->provider->flushCdn($media);
        static::assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        static::assertNull($media->getCdnFlushIdentifier());

        $media->setContext('test');
        $this->provider->flushCdn($media);
        static::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        static::assertNotNull($media->getCdnFlushIdentifier());

        $media->setContext('other');
        $this->provider->flushCdn($media);
        static::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        static::assertNotNull($media->getCdnFlushIdentifier());
        $this->provider->flushCdn($media);
        static::assertSame(CDNInterface::STATUS_WAITING, $media->getCdnStatus());
        static::assertNotNull($media->getCdnFlushIdentifier());
        $this->provider->flushCdn($media);
        static::assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        static::assertNull($media->getCdnFlushIdentifier());
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        static::assertSame('test', $provider->getProviderMetadata()->getTitle());
        static::assertSame('test.description', $provider->getProviderMetadata()->getDescription());
        static::assertNotNull($provider->getProviderMetadata()->getImage());
        static::assertSame('fa fa-file', $provider->getProviderMetadata()->getOption('class'));
        static::assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }

    public function testPostRemove(): void
    {
        $reflect = new \ReflectionClass(BaseProvider::class);
        $prop = $reflect->getProperty('clones');
        $prop->setAccessible(true);

        $provider = $this->getProvider();
        $media = new Media();
        $media->setId(1399);
        $media->setContext('default');
        $media->setProviderReference('1f981a048e7d8b671415d17e9633abc0059df394.png');
        $hash = spl_object_hash($media);

        $provider->preRemove($media);

        static::assertArrayHasKey($hash, $prop->getValue($provider));

        $media->setId(null); // Emulate an object detached from the EntityManager.
        $provider->postRemove($media);

        static::assertArrayNotHasKey($hash, $prop->getValue($provider));
        static::assertSame('default/0001/02/1f981a048e7d8b671415d17e9633abc0059df394.png', $provider->prevReferenceImage);

        $prop->setAccessible(false);
    }
}
