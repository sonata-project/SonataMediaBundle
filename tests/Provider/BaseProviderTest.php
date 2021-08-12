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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\FormBuilder;

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
        $provider = $this->getProvider();
        $provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        self::assertIsArray($provider->getTemplates());
        self::assertSame('edit.twig', $provider->getTemplate('edit'));

        self::assertInstanceOf(CDNInterface::class, $provider->getCdn());

        $provider->addFormat('small', []);

        self::assertIsArray($provider->getFormat('small'));

        $media = new Media();
        $media->setContext('test');

        self::assertSame('admin', $provider->getFormatName($media, 'admin'));
        self::assertSame('reference', $provider->getFormatName($media, 'reference'));
        self::assertSame('test_small', $provider->getFormatName($media, 'small'));
        self::assertSame('test_small', $provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath(): void
    {
        $provider = $this->getProvider();
        self::assertSame('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testFlushCdn(): void
    {
        $provider = $this->getProvider();
        $provider->addFormat('test', []);

        $media = new Media();
        $media->setId('42');
        $media->setCdnIsFlushable(true);

        $media->setContext('test');
        self::assertNull($media->getCdnFlushIdentifier());
        self::assertNull($media->getCdnStatus());
        $provider->flushCdn($media);
        self::assertTrue($media->getCdnIsFlushable());
        self::assertNotNull($media->getCdnFlushIdentifier());
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());

        $media->setContext('other');
        $provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_OK, $media->getCdnStatus());
        self::assertNull($media->getCdnFlushIdentifier());

        $media->setContext('test');
        $provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());

        $media->setContext('other');
        $provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_TO_FLUSH, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());
        $provider->flushCdn($media);
        self::assertSame(CDNInterface::STATUS_WAITING, $media->getCdnStatus());
        self::assertNotNull($media->getCdnFlushIdentifier());
        $provider->flushCdn($media);
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

class TestProvider extends BaseProvider
{
    /**
     * @var string
     */
    public $prevReferenceImage;

    public function getHelperProperties(MediaInterface $media, $format, $options = []): void
    {
        // TODO: Implement getHelperProperties() method.
    }

    public function postPersist(MediaInterface $media): void
    {
        // TODO: Implement postPersist() method.
    }

    public function buildEditForm(FormMapper $form): void
    {
        $form->add('foo');
    }

    public function buildCreateForm(FormMapper $form): void
    {
        $form->add('foo');
    }

    public function postUpdate(MediaInterface $media): void
    {
        // TODO: Implement postUpdate() method.
    }

    public function getAbsolutePath(MediaInterface $media): void
    {
        // TODO: Implement getAbsolutePath() method.
    }

    public function getReferenceImage(MediaInterface $media): string
    {
        // A copy of the code from \Sonata\MediaBundle\Provider\FileProvider::getReferenceImage()
        $this->prevReferenceImage = sprintf(
            '%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );

        return $this->prevReferenceImage;
    }

    public function generatePrivateUrl(MediaInterface $media, $format): void
    {
        // TODO: Implement generatePrivateUrl() method.
    }

    public function generatePublicUrl(MediaInterface $media, $format): void
    {
        // TODO: Implement generatePublicUrl() method.
    }

    public function getReferenceFile(MediaInterface $media): void
    {
        // TODO: Implement getReferenceFile() method.
    }

    public function preUpdate(MediaInterface $media): void
    {
        // TODO: Implement preUpdate() method.
    }

    public function prePersist(MediaInterface $media): void
    {
        // TODO: Implement prePersist() method.
    }

    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = []): void
    {
        // TODO: Implement getDownloadResponse() method.
    }

    public function buildMediaType(FormBuilder $formBuilder): void
    {
        $formBuilder->add('foo');
    }

    public function updateMetadata(MediaInterface $media, $force = false): void
    {
        // TODO: Implement updateMetadata() method.
    }

    protected function doTransform(MediaInterface $media): void
    {
        // TODO: Implement doTransform() method.
    }
}
