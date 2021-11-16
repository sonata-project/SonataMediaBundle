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

use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;

/**
 * @phpstan-extends AbstractProviderTest<ImageProvider>
 */
class ImageProviderTest extends AbstractProviderTest
{
    /**
     * @param string[] $allowedExtensions
     * @param string[] $allowedMimeTypes
     */
    public function getProvider(array $allowedExtensions = [], array $allowedMimeTypes = []): ImageProvider
    {
        /** @var MockObject&ResizerInterface $resizer */
        $resizer = $this->createMock(ResizerInterface::class);

        $adminBox = new Box(100, 100);
        $mediumBox = new Box(500, 250);
        $largeBox = new Box(1000, 500);

        $resizer->method('getBox')->will(static::onConsecutiveCalls(
            $largeBox, // first properties call
            $mediumBox,
            $largeBox,
            $mediumBox, // second call
            $mediumBox,
            $largeBox,
            $adminBox, // Third call
            $largeBox, // Fourth call
            $mediumBox,
            $largeBox,
            $largeBox, // Fifth call
            $mediumBox,
            $largeBox
        ));

        $filesystem = new Filesystem(new Local(sys_get_temp_dir().'/sonata-media-bundle/var/', true));
        $cdn = new Server('/uploads/media');
        $generator = new IdGenerator();
        $thumbnail = new FormatThumbnail('jpg');
        $adapter = new Imagine();

        /** @var MockObject&MetadataBuilderInterface $metadata */
        $metadata = $this->createMock(MetadataBuilderInterface::class);
        $metadata->method('get')->willReturn([]);

        $provider = new ImageProvider('image', $filesystem, $cdn, $generator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $adapter, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        static::assertSame('default/0011/24/ASDASDAS.png', $this->provider->getReferenceImage($media));

        static::assertSame('default/0011/24', $this->provider->generatePath($media));
        static::assertSame('/uploads/media/default/0011/24/thumb_1023456_big.png', $this->provider->generatePublicUrl($media, 'big'));
        static::assertSame('/uploads/media/default/0011/24/ASDASDAS.png', $this->provider->generatePublicUrl($media, 'reference'));

        static::assertSame('default/0011/24/ASDASDAS.png', $this->provider->generatePrivateUrl($media, 'reference'));
        static::assertSame('default/0011/24/thumb_1023456_big.png', $this->provider->generatePrivateUrl($media, 'big'));
    }

    public function testHelperProperties(): void
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', [
            'width' => 100,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $provider->addFormat('default_medium', [
            'width' => 500,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $provider->addFormat('default_large', [
            'width' => 1000,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(500);
        $media->setWidth(1500);
        $media->setContext('default');

        $srcSet = '/uploads/media/default/0001/01/thumb_10_default_medium.png 500w, /uploads/media/default/0001/01/thumb_10_default_large.png 1000w, /uploads/media/default/0001/01/ASDASDAS.png 1500w';

        $properties = $provider->getHelperProperties($media, 'default_large');

        static::assertSame('test.png', $properties['title']);
        static::assertSame(1000, $properties['width']);
        static::assertSame($srcSet, $properties['srcset']);
        static::assertSame(
            '/uploads/media/default/0001/01/thumb_10_default_large.png',
            $properties['src']
        );
        static::assertSame('(max-width: 1000px) 100vw, 1000px', $properties['sizes']);

        $properties = $provider->getHelperProperties($media, 'default_large', ['srcset' => ['default_medium']]);
        static::assertSame($srcSet, $properties['srcset']);
        static::assertSame(
            '/uploads/media/default/0001/01/thumb_10_default_large.png',
            $properties['src']
        );
        static::assertSame('(max-width: 500px) 100vw, 500px', $properties['sizes']);

        $properties = $provider->getHelperProperties($media, 'admin', [
            'width' => 150,
        ]);
        static::assertArrayNotHasKey('sizes', $properties);
        static::assertArrayNotHasKey('srcset', $properties);

        static::assertSame(150, $properties['width']);

        $properties = $provider->getHelperProperties($media, 'default_large', ['picture' => ['default_medium', 'default_large'], 'class' => 'some-class']);
        static::assertArrayHasKey('picture', $properties);
        static::assertArrayNotHasKey('srcset', $properties);
        static::assertArrayNotHasKey('sizes', $properties);
        static::assertArrayHasKey('source', $properties['picture']);
        static::assertArrayHasKey('img', $properties['picture']);
        static::assertArrayHasKey('class', $properties['picture']['img']);
        static::assertArrayHasKey('media', $properties['picture']['source'][0]);
        static::assertSame('(max-width: 500px)', $properties['picture']['source'][0]['media']);

        $properties = $provider->getHelperProperties($media, 'default_large', ['picture' => ['(max-width: 200px)' => 'default_medium', 'default_large'], 'class' => 'some-class']);
        static::assertArrayHasKey('picture', $properties);
        static::assertArrayNotHasKey('srcset', $properties);
        static::assertArrayNotHasKey('sizes', $properties);
        static::assertArrayHasKey('source', $properties['picture']);
        static::assertArrayHasKey('img', $properties['picture']);
        static::assertArrayHasKey('class', $properties['picture']['img']);
        static::assertArrayHasKey('media', $properties['picture']['source'][0]);
        static::assertSame('(max-width: 200px)', $properties['picture']['source'][0]['media']);
    }

    public function testThumbnail(): void
    {
        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        static::assertTrue($this->provider->requireThumbnails());

        $this->provider->addFormat('big', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        static::assertNotEmpty($this->provider->getFormats(), '::getFormats() return an array');

        $this->provider->generateThumbnails($media);

        static::assertSame('default/0011/24/thumb_1023456_big.png', $this->provider->generatePrivateUrl($media, 'big'));
    }

    public function testEvent(): void
    {
        $provider = $this->getProvider(['png'], ['image/png']);

        $provider->addFormat('big', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent($file);
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);
        $provider->prePersist($media);

        static::assertSame('logo.png', $media->getName(), '::getName() return the file name');
        static::assertNotNull($media->getProviderReference(), '::getProviderReference() is set');

        // post persist the media
        $provider->postPersist($media);
        $provider->postRemove($media);
    }

    public function testUpdateMetadata(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setBinaryContent($file);

        $this->provider->updateMetadata($media);

        static::assertNotNull($media->getSize());
        static::assertSame(132, $media->getHeight());
        static::assertSame(535, $media->getWidth());
    }

    public function testTransformNoExtensions(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setBinaryContent($file);

        $provider = $this->getProvider([], ['image/png']);

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('There are no allowed extensions for this image.');

        $provider->transform($media);
    }

    public function testTransformExtensionNotAllowed(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setBinaryContent($file);

        $provider = $this->getProvider(['jpg', 'jpeg'], ['image/jpg']);

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('The image extension "png" is not one of the allowed ("jpg", "jpeg")');

        $provider->transform($media);
    }

    public function testTransformNoMimeTypes(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setBinaryContent($file);

        $provider = $this->getProvider(['png']);

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('There are no allowed mime types for this image.');

        $provider->transform($media);
    }

    public function testTransformMimeTypeNotAllowed(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/logo.png');

        static::assertNotFalse($realPath);

        $file = new SymfonyFile($realPath);

        $media = new Media();
        $media->setBinaryContent($file);

        $provider = $this->getProvider(['png'], ['image/jpg', 'image/jpeg']);

        $this->expectException(UploadException::class);
        $this->expectExceptionMessage('The image mime type "image/png" is not one of the allowed ("image/jpg", "image/jpeg")');

        $provider->transform($media);
    }

    public function testMetadata(): void
    {
        static::assertSame('image', $this->provider->getProviderMetadata()->getTitle());
        static::assertSame('image.description', $this->provider->getProviderMetadata()->getDescription());
        static::assertNotNull($this->provider->getProviderMetadata()->getImage());
        static::assertSame('fa fa-picture-o', $this->provider->getProviderMetadata()->getOption('class'));
        static::assertSame('SonataMediaBundle', $this->provider->getProviderMetadata()->getDomain());
    }
}
