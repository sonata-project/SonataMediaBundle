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

class DailyMotionProviderTest extends \PHPUnit_Framework_TestCase
{

    public function getProvider()
    {
        $em = 1;
        $resizer = $this->getMock('Sonata\MediaBundle\Media\ResizerInterface', array('resize'));
        $resizer->expects($this->any())
            ->method('resize')
            ->will($this->returnValue(true));

        $adapter = $this->getMock('Gaufrette\Filesystem\Adapter');

        $file = $this->getMock('Gaufrette\Filesystem\File', array(), array($adapter));

        $filesystem = $this->getMock('Gaufrette\Filesystem\Filesystem', array('get'), array($adapter));
        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));


        $cdn = new \Sonata\MediaBundle\CDN\Server('/updoads/media');

        $provider = new \Sonata\MediaBundle\Provider\DailyMotionProvider('file', $em, $filesystem, $cdn);
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
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));
        $media->setId(10);

        $this->assertEquals('http://www.dailymotion.com/swf/video/x9wjql', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1');

        $media->setId(1023458);
        $this->assertEquals('http://www.dailymotion.com/swf/video/x9wjql', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1023456');

        $this->assertEquals('http://ak2.static.dailymotion.com/static/video/711/536/16635117:jpeg_preview_large.jpg?20100801072241', $provider->getReferenceImage($media));

        $this->assertEquals('0011/24', $provider->generatePath($media));
        $this->assertEquals('/updoads/media/0011/24/thumb_1023458_big.jpg', $provider->generatePublicUrl($media, 'big'));

    }

    public function testThumbnail()
    {
        
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $media->setId(1023458);

        $this->assertFalse($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => null, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertEquals('0011/24/thumb_1023458_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testEvent()
    {

        $provider = $this->getProvider();
        $provider->addFormat('big', array('width' => 200, 'height' => null, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('x9wjql');
        $media->setId(1023456);

        stream_wrapper_unregister('http');
        stream_wrapper_register('http', 'Sonata\\MediaBundle\\Tests\\Provider\\FakeHttpWrapper');

        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertEquals('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $provider->postRemove($media);


        stream_wrapper_restore('http');
    }
}