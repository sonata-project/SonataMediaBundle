<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Provider\DailyMotionProvider;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Buzz\Browser;
use Buzz\Message\Response;
use Imagine\Image\Box;

class DailyMotionProviderTest extends \PHPUnit_Framework_TestCase
{
    public function getProvider(Browser $browser = null)
    {
        if (!$browser) {
            $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        }

        $resizer = $this->getMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));
        $resizer->expects($this->any())->method('getBox')->will($this->returnValue(new Box(100, 100)));

        $adapter = $this->getMock('Gaufrette\Adapter');

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $file = $this->getMock('Gaufrette\File', array(), array('foo', $filesystem));
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $provider = new DailyMotionProvider('file', $filesystem, $cdn, $generator, $thumbnail, $browser, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $this->assertEquals('http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241', $provider->getReferenceImage($media));

        $media->setId(1023458);

        $this->assertEquals('default/0011/24', $provider->generatePath($media));
        $this->assertEquals('/uploads/media/default/0011/24/thumb_1023458_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail()
    {
        $response = $this->getMock('Buzz\Message\MessageInterface');
        $response->expects($this->once())->method('getContent')->will($this->returnValue('content'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();

        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $media = new Media;
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setContext('default');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $media->setId(1023458);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => null, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertEquals('default/0011/24/thumb_1023458_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testTransformWithSig()
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__.'/../fixtures/valid_dailymotion.txt'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => null, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('x9wjql');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertEquals('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertEquals('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public function testTransformWithUrl()
    {
        $response = new Response();
        $response->setContent(file_get_contents(__DIR__.'/../fixtures/valid_dailymotion.txt'));

        $browser = $this->getMockBuilder('Buzz\Browser')->getMock();
        $browser->expects($this->once())->method('get')->will($this->returnValue($response));

        $provider = $this->getProvider($browser);

        $provider->addFormat('big', array('width' => 200, 'height' => null, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('http://www.dailymotion.com/video/x9wjql_asdasdasdsa_asdsds');
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertEquals('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertEquals('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');
    }

    public function testForm()
    {
        if (!class_exists('Sonata\AdminBundle\Form\FormMapper')) {
            $this->markTestSkipped("AdminBundle doesn't seem to be installed");
        }

        $provider = $this->getProvider();

        $admin = $this->getMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('message'));

        $formMapper = $this->getMock('Sonata\AdminBundle\Form\FormMapper', array('add', 'getAdmin'), array(), '', false);
        $formMapper->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnValue(null));

        $provider->buildCreateForm($formMapper);

        $provider->buildEditForm($formMapper);
    }

    public function testHelperProperies()
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', array('width' => 100));
        $media = new Media;
        $media->setName('Les tests');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);
        $media->setWidth(100);

        $properties = $provider->getHelperProperties($media, 'admin');

        $this->assertInternalType('array', $properties);
        $this->assertEquals(100, $properties['height']);
        $this->assertEquals(100, $properties['width']);
    }
}
