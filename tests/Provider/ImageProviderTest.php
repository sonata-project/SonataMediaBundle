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
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\MockObject\Stub\Stub;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class ImageProviderTest extends AbstractProviderTest
{
    public function getProvider(array $allowedExtensions = [], array $allowedMimeTypes = [], ?Stub $box = null): MediaProviderInterface
    {
        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->method('resize')->willReturn(true);
        if ($box) {
            $resizer->method('getBox')->will($box);
        }

        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(File::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();
        $filesystem->method('get')->willReturn($file);

        $cdn = new Server('/uploads/media');

        $generator = new IdGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $size = $this->createMock(BoxInterface::class);
        $size->method('getWidth')->willReturn(100);
        $size->method('getHeight')->willReturn(100);

        $image = $this->createMock(ImageInterface::class);
        $image->method('getSize')->willReturn($size);

        $adapter = $this->createMock(ImagineInterface::class);
        $adapter->method('open')->willReturn($image);

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new ImageProvider('image', $filesystem, $cdn, $generator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $adapter, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        static::assertSame('default/0011/24/ASDASDAS.png', $provider->getReferenceImage($media));

        static::assertSame('default/0011/24', $provider->generatePath($media));
        static::assertSame('/uploads/media/default/0011/24/thumb_1023456_big.png', $provider->generatePublicUrl($media, 'big'));
        static::assertSame('/uploads/media/default/0011/24/ASDASDAS.png', $provider->generatePublicUrl($media, 'reference'));

        static::assertSame('default/0011/24/ASDASDAS.png', $provider->generatePrivateUrl($media, 'reference'));
        static::assertSame('default/0011/24/thumb_1023456_big.png', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testHelperOptions(): void
    {
        $this->expectException(\LogicException::class);

        $provider = $this->getProvider([], [], static::returnValue(new Box(50, 50)));
        $media = new Media();

        $provider->getHelperProperties($media, 'any_format', ['srcset' => [], 'picture' => []]);
    }

    public function testHelperProperties(): void
    {
        $adminBox = new Box(100, 100);
        $mediumBox = new Box(500, 250);
        $largeBox = new Box(1000, 500);

        $provider = $this->getProvider(
            [],
            [],
            static::onConsecutiveCalls(
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
            )
        );

        $provider->addFormat('admin', ['width' => 100]);
        $provider->addFormat('default_medium', ['width' => 500]);
        $provider->addFormat('default_large', ['width' => 1000]);

        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(500);
        $media->setWidth(1500);
        $media->setContext('default');

        $srcSet = '/uploads/media/default/0001/01/thumb_10_default_medium.png 500w, /uploads/media/default/0001/01/thumb_10_default_large.png 1000w, /uploads/media/default/0001/01/ASDASDAS.png 1500w';

        $properties = $provider->getHelperProperties($media, 'default_large');

        static::assertIsArray($properties);
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
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        static::assertTrue($provider->requireThumbnails());

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        static::assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        static::assertSame('default/0011/24/thumb_1023456_big.png', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testEvent(): void
    {
        $provider = $this->getProvider();

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../Fixtures/logo.png'));

        $media = new Media();
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

    public function testTransformFormatNotSupported(): void
    {
        $provider = $this->getProvider();

        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../Fixtures/logo.png'));

        $media = new Media();
        $media->setBinaryContent($file);

        static::assertNull($provider->transform($media));
        static::assertNull($media->getWidth(), 'Width staid null');
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        static::assertSame('image', $provider->getProviderMetadata()->getTitle());
        static::assertSame('image.description', $provider->getProviderMetadata()->getDescription());
        static::assertNotNull($provider->getProviderMetadata()->getImage());
        static::assertSame('fa fa-picture-o', $provider->getProviderMetadata()->getOption('class'));
        static::assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }
}
