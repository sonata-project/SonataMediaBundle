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

use Buzz\Browser;
use Buzz\Message\AbstractMessage;
use Buzz\Message\Response;
use Gaufrette\Adapter;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Imagine\Image\Box;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\DefaultGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\YouTubeProvider;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class YouTubeProviderTest extends AbstractProviderTest
{
    public function getProvider(Browser $browser = null)
    {
        if (!$browser) {
            $browser = $this->createMock(Browser::class);
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

        $provider = new YouTubeProvider('file', $filesystem, $cdn, $generator, $thumbnail, $browser, $metadata);
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
        $response = $this->createMock(AbstractMessage::class);
        $response->expects($this->once())->method('getContent')->will($this->returnValue('content'));

        $browser = $this->createMock(Browser::class);

        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $media = new Media();
        $media->setProviderName('youtube');
        $media->setProviderReference('BDYAbAtaDzA');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"provider_url": "http:\/\/www.youtube.com\/", "title": "Nono le petit robot", "html": "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\"><\/param><param name=\"allowFullScreen\" value=\"true\"><\/param><param name=\"allowscriptaccess\" value=\"always\"><\/param><embed src=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\" type=\"application\/x-shockwave-flash\" width=\"425\" height=\"344\" allowscriptaccess=\"always\" allowfullscreen=\"true\"><\/embed><\/object>", "author_name": "timan38", "height": 344, "thumbnail_width": 480, "width": 425, "version": "1.0", "author_url": "http:\/\/www.youtube.com\/user\/timan38", "provider_name": "YouTube", "thumbnail_url": "http:\/\/i3.ytimg.com\/vi\/BDYAbAtaDzA\/hqdefault.jpg", "type": "video", "thumbnail_height": 360}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertSame('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig(): void
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__.'/../fixtures/valid_youtube.txt'));

        $browser = $this->createMock(Browser::class);
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

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
    public function testTransformWithUrl($url): void
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__.'/../fixtures/valid_youtube.txt'));

        $browser = $this->createMock(Browser::class);
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

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

    public static function getUrls()
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

    public function testForm(): void
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

        $this->assertInternalType('array', $properties);
        $this->assertSame(100, $properties['player_parameters']['height']);
        $this->assertSame(100, $properties['player_parameters']['width']);
    }

    public function testGetReferenceUrl(): void
    {
        $media = new Media();
        $media->setProviderReference('123456');
        $this->assertEquals('https://www.youtube.com/watch?v=123456', $this->getProvider()->getReferenceUrl($media));
    }
}
