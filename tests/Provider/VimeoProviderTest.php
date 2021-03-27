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
use Sonata\MediaBundle\Provider\VimeoProvider;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class VimeoProviderTest extends AbstractProviderTest
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

        $provider = new VimeoProvider('vimeo', $filesystem, $cdn, $generator, $thumbnail, $client, $requestFactory, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('Blinky™');
        $media->setProviderName('vimeo');
        $media->setProviderReference('21216091');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Vimeo","provider_url":"http:\/\/vimeo.com\/","title":"Blinky\u2122","author_name":"Ruairi Robinson","author_url":"http:\/\/vimeo.com\/ruairirobinson","is_plus":"1","html":"<iframe src=\"http:\/\/player.vimeo.com\/video\/21216091\" width=\"1920\" height=\"1080\" frameborder=\"0\"><\/iframe>","width":"1920","height":"1080","duration":"771","description":"","thumbnail_url":"http:\/\/b.vimeocdn.com\/ts\/136\/375\/136375440_1280.jpg","thumbnail_width":1280,"thumbnail_height":720,"video_id":"21216091"}', true));

        $media->setId(1023457);
        $this->assertSame('http://b.vimeocdn.com/ts/136/375/136375440_1280.jpg', $provider->getReferenceImage($media));

        $this->assertSame('default/0011/24', $provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail(): void
    {
        $request = $this->createStub(RequestInterface::class);

        $messageFactory = $this->createMock(RequestFactoryInterface::class);
        $messageFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))->willReturn($this->createResponse('content'));

        $provider = $this->getProvider($client, $messageFactory);

        $media = new Media();
        $media->setName('Blinky™');
        $media->setProviderName('vimeo');
        $media->setProviderReference('21216091');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Vimeo","provider_url":"http:\/\/vimeo.com\/","title":"Blinky\u2122","author_name":"Ruairi Robinson","author_url":"http:\/\/vimeo.com\/ruairirobinson","is_plus":"1","html":"<iframe src=\"http:\/\/player.vimeo.com\/video\/21216091\" width=\"1920\" height=\"1080\" frameborder=\"0\"><\/iframe>","width":"1920","height":"1080","duration":"771","description":"","thumbnail_url":"http:\/\/b.vimeocdn.com\/ts\/136\/375\/136375440_1280.jpg","thumbnail_width":1280,"thumbnail_height":720,"video_id":"21216091"}', true));

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

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_vimeo.txt')));

        $provider = $this->getProvider($client, $requestFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent('BDYAbAtaDzA');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);
        $provider->prePersist($media);

        $this->assertSame('Blinky™', $media->getName(), '::getName() return the file name');
        $this->assertSame('BDYAbAtaDzA', $media->getProviderReference(), '::getProviderReference() is set');
    }

    /**
     * @dataProvider getTransformWithUrlMedia
     */
    public function testTransformWithUrl(Media $media): void
    {
        $request = $this->createStub(RequestInterface::class);

        $messageFactory = $this->createMock(RequestFactoryInterface::class);
        $messageFactory->expects($this->once())->method('createRequest')->willReturn($request);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))
            ->willReturn($this->createResponse(file_get_contents(__DIR__.'/../Fixtures/valid_vimeo.txt')));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        // pre persist the media
        $provider->transform($media);
        $provider->prePersist($media);

        $this->assertSame('Blinky™', $media->getName(), '::getName() return the file name');
        $this->assertSame('012341231', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public function getTransformWithUrlMedia(): array
    {
        $mediaWebsite = new Media();
        $mediaWebsite->setContext('default');
        $mediaWebsite->setBinaryContent('https://vimeo.com/012341231');
        $mediaWebsite->setId(1023456);

        $mediaPlayer = new Media();
        $mediaPlayer->setContext('default');
        $mediaPlayer->setBinaryContent('https://player.vimeo.com/video/012341231');
        $mediaPlayer->setId(1023456);

        return [
            'transform with website url' => [$mediaWebsite],
            'transform with player url' => [$mediaPlayer],
        ];
    }

    public function testGetMetadataException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve the video information for :012341231');
        $this->expectExceptionCode(12);

        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())->method('sendRequest')->will($this->throwException(new \RuntimeException('First error on get', 12)));

        $provider = $this->getProvider($client);

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $media = new Media();
        $media->setBinaryContent('https://vimeo.com/012341231');
        $media->setId(1023456);

        $method = new \ReflectionMethod($provider, 'getMetadata');
        $method->setAccessible(true);

        $method->invokeArgs($provider, [$media, '012341231']);
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

    public function testHelperProperies(): void
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
        $this->assertSame(100, $properties['height']);
        $this->assertSame(100, $properties['width']);
    }

    public function testGetReferenceUrl(): void
    {
        $media = new Media();
        $media->setProviderReference('123456');
        $this->assertSame('https://vimeo.com/123456', $this->getProvider()->getReferenceUrl($media));
    }

    public function testMetadata(): void
    {
        $provider = $this->getProvider();

        $this->assertSame('vimeo', $provider->getProviderMetadata()->getTitle());
        $this->assertSame('vimeo.description', $provider->getProviderMetadata()->getDescription());
        $this->assertNotNull($provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-vimeo-square', $provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }
}
