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
use Sonata\MediaBundle\Provider\DailyMotionProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class DailyMotionProviderTest extends AbstractProviderTest
{
    public function getProvider(?object $client = null, ?RequestFactoryInterface $requestFactory = null): MediaProviderInterface
    {
        if (null === $client) {
            $client = $this->createStub(ClientInterface::class);
        }

        if (null === $requestFactory) {
            $requestFactory = $this->createStub(RequestFactoryInterface::class);
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

        $provider = new DailyMotionProvider('file', $filesystem, $cdn, $generator, $thumbnail, $client, $requestFactory, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $media = new Media();
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $this->assertSame('http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241', $this->provider->getReferenceImage($media));

        $media->setId(1023458);

        $this->assertSame('default/0011/24', $this->provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023458_big.jpg', $this->provider->generatePublicUrl($media, 'big'));
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
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $media->setId(1023458);

        $this->assertTrue($provider->requireThumbnails());

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023458_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig(): void
    {
        $request = $this->createStub(RequestInterface::class);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_dailymotion.txt')));

        $provider = $this->getProvider($client, $requestFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent('x9wjql');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertSame('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider dataTransformWithUrl
     */
    public function testTransformWithUrl(string $url): void
    {
        $request = $this->createStub(RequestInterface::class);

        $messageFactory = $this->createMock(RequestFactoryInterface::class);
        $messageFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_dailymotion.txt')));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent($url);
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertSame('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public function dataTransformWithUrl(): array
    {
        return [
            ['http://www.dailymotion.com/video/x9wjql_asdasdasdsa_asdsds'],
            ['http://www.dailymotion.com/video/x9wjql'],
            ['https://www.dailymotion.com/video/x9wjql'],
            ['www.dailymotion.com/video/x9wjql'],
            ['x9wjql'],
        ];
    }

    public function testGetMetadataException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve the video information for :x9wjql');
        $this->expectExceptionCode(12);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->will($this->throwException(new \RuntimeException('First error on get', 12)));

        $provider = $this->getProvider($client);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setBinaryContent('x9wjql');
        $media->setId(1023456);

        $method = new \ReflectionMethod($provider, 'getMetadata');
        $method->setAccessible(true);

        $method->invokeArgs($provider, [$media, 'x9wjql']);
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
        $this->provider->addFormat('admin', ['width' => 100]);
        $media = new Media();
        $media->setName('Les tests');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);
        $media->setWidth(100);

        $properties = $this->provider->getHelperProperties($media, 'admin');

        $this->assertIsArray($properties);
        $this->assertSame(100, $properties['height']);
        $this->assertSame(100, $properties['width']);
    }

    public function testGetReferenceUrl(): void
    {
        $media = new Media();
        $media->setProviderReference('123456');
        $this->assertSame('http://www.dailymotion.com/video/123456', $this->provider->getReferenceUrl($media));
    }
}
