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

    public function testTransformUploadBase64(): void
    {
        $provider = $this->getProvider(['png'], ['image/png']);

        $imageBase64 = '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';

        $tmp = tempnam('/tmp', 'tmp');
        \assert(false !== $tmp);

        file_put_contents($tmp, base64_decode($imageBase64, true));

        $media = new Media();
        $media->setBinaryContent($tmp);

        $provider->transform($media);

        static::assertNotNull($media->getSize());
        static::assertSame('image/png', $media->getContentType());
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
