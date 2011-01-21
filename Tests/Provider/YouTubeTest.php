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

class YoutubeProviderTest extends \PHPUnit_Framework_TestCase
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


        $provider = new \Sonata\MediaBundle\Provider\YouTubeProvider('youtube', $em, $settings);


        $media = new Media;
        $media->setName('Nono le petit robot');
        $media->setProviderName('youtube');
        $media->setProviderReference('BDYAbAtaDzA');
        $media->setProviderMetadata(json_decode('{"provider_url": "http:\/\/www.youtube.com\/", "title": "Nono le petit robot", "html": "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\"><\/param><param name=\"allowFullScreen\" value=\"true\"><\/param><param name=\"allowscriptaccess\" value=\"always\"><\/param><embed src=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\" type=\"application\/x-shockwave-flash\" width=\"425\" height=\"344\" allowscriptaccess=\"always\" allowfullscreen=\"true\"><\/embed><\/object>", "author_name": "timan38", "height": 344, "thumbnail_width": 480, "width": 425, "version": "1.0", "author_url": "http:\/\/www.youtube.com\/user\/timan38", "provider_name": "YouTube", "thumbnail_url": "http:\/\/i3.ytimg.com\/vi\/BDYAbAtaDzA\/hqdefault.jpg", "type": "video", "thumbnail_height": 360}', true));
        $media->setId(10);

        $this->assertEquals('http://www.youtube.com/v/BDYAbAtaDzA', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1');

        $media->setId(1023457);
        $this->assertEquals('http://www.youtube.com/v/BDYAbAtaDzA', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1023456');

        $this->assertEquals('http://i3.ytimg.com/vi/BDYAbAtaDzA/hqdefault.jpg', $provider->getReferenceImage($media));

        $this->assertEquals('/fake/path/0011/24', $provider->generatePrivatePath($media));
        $this->assertEquals('/updoads/media/0011/24', $provider->generatePublicPath($media));
        $this->assertEquals('/updoads/media/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));

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


        $provider = new \Sonata\MediaBundle\Provider\YouTubeProvider('youtube', $em, $settings);


        $media = new Media;
        $media->setProviderName('youtube');
        $media->setProviderReference('BDYAbAtaDzA');
        $media->setProviderMetadata(json_decode('{"provider_url": "http:\/\/www.youtube.com\/", "title": "Nono le petit robot", "html": "<object width=\"425\" height=\"344\"><param name=\"movie\" value=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\"><\/param><param name=\"allowFullScreen\" value=\"true\"><\/param><param name=\"allowscriptaccess\" value=\"always\"><\/param><embed src=\"http:\/\/www.youtube.com\/v\/BDYAbAtaDzA?fs=1\" type=\"application\/x-shockwave-flash\" width=\"425\" height=\"344\" allowscriptaccess=\"always\" allowfullscreen=\"true\"><\/embed><\/object>", "author_name": "timan38", "height": 344, "thumbnail_width": 480, "width": 425, "version": "1.0", "author_url": "http:\/\/www.youtube.com\/user\/timan38", "provider_name": "YouTube", "thumbnail_url": "http:\/\/i3.ytimg.com\/vi\/BDYAbAtaDzA\/hqdefault.jpg", "type": "video", "thumbnail_height": 360}', true));

        $media->setId(1023457);

        try {
            $provider->generateThumbnails($media);
            $this->assertFalse(true, '::generateThumbnails() must generate a RuntimeException with no formats defined');
        } catch (\Exception $e) {

            $this->assertInstanceOf('\RuntimeException', $e);
        }

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        // clean previous test
        if(is_file(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023456_big.jpg')) {

            unlink(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023456_big.jpg');
        }

        $provider->generateThumbnails($media);

        $this->assertEquals(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
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


        $provider = new \Sonata\MediaBundle\Provider\YouTubeProvider('youtube', $em, $settings);

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('BDYAbAtaDzA');
        $media->setId(1023456);

        stream_wrapper_unregister('http');
        stream_wrapper_register('http', 'Bundle\\Sonata\\MediaBundle\\Tests\\Provider\\FakeHttpWrapper');
        
        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('Nono le petit robot', $media->getName(), '::getName() return the file name');
        $this->assertEquals('BDYAbAtaDzA', $media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $this->assertFileExists($provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return a valid file');

        $provider->postRemove($media);

        $this->assertFileNotExists($provider->generatePrivateUrl($media, 'big'), '::postRemove() remove the thumbnail');


        stream_wrapper_restore('http');
    }

}