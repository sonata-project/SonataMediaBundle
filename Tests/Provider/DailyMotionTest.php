<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\MediaBundle\Tests\Provider;

use Bundle\MediaBundle\Tests\Entity\Media;

class DailyMotionProviderTest extends \PHPUnit_Framework_TestCase
{

    public function testProvider()
    {

        $em = 1;
        $settings = array (
            'cdn_enabled'   => true,
            'cdn_path'      => 'http://here.com',
            'private_path'  => '/fake/path',
            'public_path'   => '/updoads/media',
        );


        $provider = new \Bundle\MediaBundle\Provider\DailyMotion('dailymotion', $em, $settings);


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

        $this->assertEquals('/fake/path/0011/24', $provider->generatePrivatePath($media));
        $this->assertEquals('http://here.com/updoads/media/0011/24', $provider->generatePublicPath($media));
        $this->assertEquals('http://here.com/updoads/media/0011/24/thumb_1023458_big.jpg', $provider->generatePublicUrl($media, 'big'));

    }

    public function testThumbnail()
    {


        $em = 1;
        $settings = array (
            'cdn_enabled'   => true,
            'cdn_path'      => 'http://here.com',
            'private_path'  => sys_get_temp_dir().'/media_bundle_test',
            'public_path'   => '/updoads/media',
        );


        $provider = new \Bundle\MediaBundle\Provider\DailyMotion('dailymotion', $em, $settings);


        $media = new Media;
        $media->setName('les tests fonctionnels - Symfony Live 2009');
        $media->setProviderName('dailymotion');
        $media->setProviderReference('x9wjql');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"http:\/\/www.dailymotion.com","title":"Thomas Rabaix - les tests fonctionnels - Symfony Live 2009","author_name":"Guillaume Pon\u00e7on","author_url":"http:\/\/www.dailymotion.com\/phptv","width":480,"height":270,"html":"<iframe src=\"http:\/\/www.dailymotion.com\/embed\/video\/x9wjql\" width=\"480\" height=\"270\" frameborder=\"0\"><\/iframe>","thumbnail_url":"http:\/\/ak2.static.dailymotion.com\/static\/video\/711\/536\/16635117:jpeg_preview_large.jpg?20100801072241","thumbnail_width":426.666666667,"thumbnail_height":240}', true));

        $media->setId(1023458);

        try {
            $provider->generateThumbnails($media);
            $this->assertFalse(true, '::generateThumbnails() must generate a RuntimeException with no formats defined');
        } catch (\Exception $e) {

            $this->assertInstanceOf('\RuntimeException', $e);
        }

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        // clean previous test
        if(is_file(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023458_big.jpg')) {

            unlink(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023458_big.jpg');
        }

        $provider->generateThumbnails($media);

        $this->assertEquals(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023458_big.jpg', $provider->generatePrivateUrl($media, 'big'));
        $this->assertFileExists($provider->generatePrivateUrl($media, 'big'), '::generateThumbnails() created the big thumbnail');
    }

    public function testEvent() {
        $em = 1;
        $settings = array (
            'cdn_enabled'   => true,
            'cdn_path'      => 'http://here.com',
            'private_path'  => sys_get_temp_dir().'/media_bundle_test',
            'public_path'   => '/updoads/media',
        );


        $provider = new \Bundle\MediaBundle\Provider\DailyMotion('dailymotion', $em, $settings);

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('x9wjql');
        $media->setId(1023456);

        stream_wrapper_unregister('http');
        stream_wrapper_register('http', 'Bundle\\MediaBundle\\Tests\\Provider\\FakeHttpWrapper');

        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('Thomas Rabaix - les tests fonctionnels - Symfony Live 2009', $media->getName(), '::getName() return the file name');
        $this->assertEquals('x9wjql', $media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $this->assertFileExists($provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return a valid file');

        $provider->postRemove($media);

        $this->assertFileNotExists($provider->generatePrivateUrl($media, 'big'), '::postRemove() remove the thumbnail');


        stream_wrapper_restore('http');
    }
}