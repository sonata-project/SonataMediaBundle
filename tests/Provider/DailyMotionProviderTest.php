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
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Imagine\Image\Box;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\DefaultGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\DailyMotionProvider;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class DailyMotionProviderTest extends AbstractProviderTest
{
    public function getProvider(HttpClient $client = null, MessageFactory $messageFactory = null)
    {
        if (null === $client) {
            $client = $this->createMock('Http\Client\HttpClient');
        }

        if (null === $messageFactory) {
            $messageFactory = $this->createMock('Http\Message\MessageFactory');
        }

        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));
        $resizer->expects($this->any())->method('getBox')->will($this->returnValue(new Box(100, 100)));

        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(File::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new Server('/uploads/media');

        $generator = new DefaultGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new DailyMotionProvider('file', $filesystem, $cdn, $generator, $thumbnail, $client, $messageFactory, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $this->assertSame('http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241', $provider->getReferenceImage($media));

        $media->setId(1023458);

        $this->assertSame('default/0011/24', $provider->generatePath($media));
        $this->assertSame('/uploads/media/default/0011/24/thumb_1023458_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail()
    {
        $request = $this->createMock('Psr\Http\Message\RequestInterface');

        $messageFactory = $this->createMock('Http\Message\MessageFactory');
        $messageFactory->expects($this->once())->method('createRequest')->will($this->returnValue($request));

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())->method('getBody')->will($this->returnValue('content'));

        $client = $this->createMock('Http\Client\HttpClient');
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))->will($this->returnValue($response));

        $provider = $this->getProvider($client, $messageFactory);

        $media = new Media();
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $media->setId(1023458);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023458_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig()
    {
        $request = $this->createMock('Psr\Http\Message\RequestInterface');

        $messageFactory = $this->createMock('Http\Message\MessageFactory');
        $messageFactory->expects($this->once())->method('createRequest')->will($this->returnValue($request));

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())->method('getBody')->will($this->returnValue(
            file_get_contents(__DIR__.'/../fixtures/valid_dailymotion.txt')
        ));

        $client = $this->createMock('Http\Client\HttpClient');
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))->will($this->returnValue($response));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $media = new Media();
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
    public function testTransformWithUrl($url)
    {
        $request = $this->createMock('Psr\Http\Message\RequestInterface');

        $messageFactory = $this->createMock('Http\Message\MessageFactory');
        $messageFactory->expects($this->once())->method('createRequest')->will($this->returnValue($request));

        $response = $this->createMock('Psr\Http\Message\ResponseInterface');
        $response->expects($this->once())->method('getBody')->will($this->returnValue(
            file_get_contents(__DIR__.'/../fixtures/valid_dailymotion.txt')
        ));

        $client = $this->createMock('Http\Client\HttpClient');
        $client->expects($this->once())->method('sendRequest')->with($this->equalTo($request))->will($this->returnValue($response));

        $provider = $this->getProvider($client, $messageFactory);

        $provider->addFormat('big', ['width' => 200, 'height' => null, 'constraint' => true]);

        $media = new Media();
        $media->setBinaryContent($url);
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertSame('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public function dataTransformWithUrl()
    {
        return [
            ['http://www.dailymotion.com/video/x9wjql_asdasdasdsa_asdsds'],
            ['http://www.dailymotion.com/video/x9wjql'],
            ['https://www.dailymotion.com/video/x9wjql'],
            ['www.dailymotion.com/video/x9wjql'],
            ['x9wjql'],
        ];
    }

    public function testForm()
    {
        $provider = $this->getProvider();

        $admin = $this->createMock(AdminInterface::class);
        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('message'));

        $formMapper = $this->getMockBuilder(FormMapper::class)
            ->setMethods(['add', 'getAdmin'])
            ->disableOriginalConstructor()
            ->getMock();
        $formMapper->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnValue(null));

        $provider->buildCreateForm($formMapper);

        $provider->buildEditForm($formMapper);
    }

    public function testHelperProperies()
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

        $this->assertInternalType('array', $properties);
        $this->assertSame(100, $properties['height']);
        $this->assertSame(100, $properties['width']);
    }

    public function testGetReferenceUrl()
    {
        $media = new Media();
        $media->setProviderReference('123456');
        $this->assertEquals('http://www.dailymotion.com/video/123456', $this->getProvider()->getReferenceUrl($media));
    }
}
