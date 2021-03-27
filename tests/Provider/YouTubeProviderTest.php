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
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\YouTubeProvider;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class YouTubeProviderTest extends AbstractProviderTest
{
    public function getProvider(?object $client = null, ?RequestFactoryInterface $messageFactory = null): MediaProviderInterface
    {
        if (null === $client) {
            $client = $this->createStub(ClientInterface::class);
        }

        if (null === $messageFactory) {
            $messageFactory = $this->createStub(RequestFactoryInterface::class);
        }

        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->method('resize')->willReturn(true);
        $resizer->method('getBox')->willReturn(new Box(100, 100));

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

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new YouTubeProvider('youtube', $filesystem, $cdn, $generator, $thumbnail, $client, $messageFactory, $metadata, false);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('Nono le petit robot');
        $media->setProviderName('youtube');
        $media->setProviderReference('BDYAbAtaDzA');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"provider_url": "http:\/\/www.youtube.com\/", "title": "Nono le petit robot", "html": "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\"><\/param><param name=\"allowFullScreen\" value=\"true\"><\/param><param name=\"allowscriptaccess\" value=\"always\"><\/param><embed src=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\" type=\"application\/x-shockwave-flash\" width=\"425\" height=\"344\" allowscriptaccess=\"always\" allowfullscreen=\"true\"><\/embed><\/object>", "author_name": "timan38", "height": 344, "thumbnail_width": 480, "width": 425, "version": "1.0", "author_url": "http:\/\/www.youtube.com\/user\/timan38", "provider_name": "YouTube", "thumbnail_url": "http:\/\/i3.ytimg.com\/vi\/BDYAbAtaDzA\/hqdefault.jpg", "type": "video", "thumbnail_height": 360}', true));

        $media->setId(1023457);

        $this->assertSame('http://i3.ytimg.com/vi/BDYAbAtaDzA/hqdefault.jpg', $provider->getReferenceImage($media));

        $this->assertSame('default/0011/24', $provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail(): void
    {
        $request = $this->createStub(RequestInterface::class);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))->willReturn($this->createResponse('content'));

        $provider = $this->getProvider($client, $requestFactory);

        $media = new Media();
        $media->setProviderName('youtube');
        $media->setProviderReference('BDYAbAtaDzA');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"provider_url": "http:\/\/www.youtube.com\/", "title": "Nono le petit robot", "html": "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\"><\/param><param name=\"allowFullScreen\" value=\"true\"><\/param><param name=\"allowscriptaccess\" value=\"always\"><\/param><embed src=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\" type=\"application\/x-shockwave-flash\" width=\"425\" height=\"344\" allowscriptaccess=\"always\" allowfullscreen=\"true\"><\/embed><\/object>", "author_name": "timan38", "height": 344, "thumbnail_width": 480, "width": 425, "version": "1.0", "author_url": "http:\/\/www.youtube.com\/user\/timan38", "provider_name": "YouTube", "thumbnail_url": "http:\/\/i3.ytimg.com\/vi\/BDYAbAtaDzA\/hqdefault.jpg", "type": "video", "thumbnail_height": 360}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails());

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig(): void
    {
        $request = $this->createStub(RequestInterface::class);

        $messageFactory = $this->createMock(RequestFactoryInterface::class);
        $messageFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_youtube.txt')));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent('BDYAbAtaDzA');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Nono le petit robot', $media->getName(), '::getName() return the file name');
        $this->assertSame('BDYAbAtaDzA', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider getUrls
     */
    public function testTransformWithUrl(string $url): void
    {
        $request = $this->createStub(RequestInterface::class);

        $messageFactory = $this->createMock(RequestFactoryInterface::class);
        $messageFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_youtube.txt')));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent($url);
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Nono le petit robot', $media->getName(), '::getName() return the file name');
        $this->assertSame('BDYAbAtaDzA', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public static function getUrls(): array
    {
        return [
        ['BDYAbAtaDzA'],
        ['http://www.youtube.com/watch?v=BDYAbAtaDzA&feature=feedrec_grec_index'],
        ['http://www.youtube.com/v/BDYAbAtaDzA?fs=1&amp;hl=en_US&amp;rel=0'],
        ['http://www.youtube.com/watch?v=BDYAbAtaDzA#t=0m10s'],
        ['http://www.youtube.com/embed/BDYAbAtaDzA?rel=0'],
        ['http://www.youtube.com/watch?v=BDYAbAtaDzA'],
        ['http://www.m.youtube.com/watch?v=BDYAbAtaDzA'],
        ['http://m.youtube.com/watch?v=BDYAbAtaDzA'],
        ['https://www.m.youtube.com/watch?v=BDYAbAtaDzA'],
        ['https://m.youtube.com/watch?v=BDYAbAtaDzA'],
        ['http://youtu.be/BDYAbAtaDzA'],
        ];
    }

    public function testGetMetadataException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve the video information for :BDYAbAtaDzA');
        $this->expectExceptionCode(12);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->will($this->throwException(new \RuntimeException('First error on get', 12)));

        $provider = $this->getProvider($client);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setBinaryContent('BDYAbAtaDzA');
        $media->setId(1023456);

        $method = new \ReflectionMethod($provider, 'getMetadata');
        $method->setAccessible(true);

        $method->invokeArgs($provider, [$media, 'BDYAbAtaDzA']);
    }

    public function testForm(): void
    {
        $provider = $this->getProvider();

        $admin = $this->createMock(AdminInterface::class);
        $admin
            ->method('trans')
            ->willReturn('message');

        $formMapper = $this->createMock(FormMapper::class);
        $formMapper->expects($this->exactly(8))
            ->method('add')
            ->willReturn(null);

        $provider->buildCreateForm($formMapper);

        $provider->buildEditForm($formMapper);
    }

    public function testHelperProperties(): void
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', ['width' => 100]);
        $media = new Media();
        $media->setName('Les tests');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);
        $media->setWidth(100);

        $properties = $provider->getHelperProperties($media, 'admin');

        $this->assertIsArray($properties);
        $this->assertSame(100, $properties['player_parameters']['height']);
        $this->assertSame(100, $properties['player_parameters']['width']);
    }

    public function testGetReferenceUrl(): void
    {
        $media = new Media();
        $media->setProviderReference('123456');
        $this->assertSame('https://www.youtube.com/watch?v=123456', $this->getProvider()->getReferenceUrl($media));
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        $this->assertSame('youtube', $provider->getProviderMetadata()->getTitle());
        $this->assertSame('youtube.description', $provider->getProviderMetadata()->getDescription());
        $this->assertNotNull($provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-youtube', $provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }
}
