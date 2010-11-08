<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\MediaBundle\Tests\Provider;

use Bundle\MediaBundle\Tests\Entity\Media;

class ImageProviderTest extends \PHPUnit_Framework_TestCase
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


        $provider = new \Bundle\MediaBundle\Provider\Image('image', $em, $settings);

        
        $media = new Media;
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);

        $this->assertEquals('/fake/path/0001/01/ASDASDAS.png', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1');

        $media->setId(1023456);
        $this->assertEquals('/fake/path/0011/24/ASDASDAS.png', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1023456');

        $this->assertEquals('/fake/path/0011/24/ASDASDAS.png', $provider->getReferenceImage($media));

        $this->assertEquals('/fake/path/0011/24', $provider->generatePrivatePath($media));
        $this->assertEquals('http://here.com/updoads/media/0011/24', $provider->generatePublicPath($media));
        $this->assertEquals('http://here.com/updoads/media/0011/24/thumb_1023456_big.jpg', $provider->generatePublicUrl($media, 'big'));

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


        $provider = new \Bundle\MediaBundle\Provider\Image('image', $em, $settings);

        $media = new Media;
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);


        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/logo.png'));

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

        copy($file->getPath(), $provider->getReferenceImage($media));
        
        $provider->generateThumbnails($media);

        $this->assertEquals(sys_get_temp_dir().'/media_bundle_test/0011/24/thumb_1023456_big.jpg', $provider->generatePrivateUrl($media, 'big'));
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


        $provider = new \Bundle\MediaBundle\Provider\Image('image', $em, $settings);

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));
        
        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/logo.png'));

        $media = new Media;
        $media->setBinaryContent($file);
        $media->setId(1023456);

        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('logo.png', $media->getName(), '::getName() return the file name');
        $this->assertNotNull($media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $this->assertFileExists($provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return a valid file');

        $provider->postRemove($media);

        $this->assertFileNotExists($provider->generatePrivateUrl($media, 'big'), '::postRemove() remove the thumbnail');
        $this->assertFileNotExists($provider->getReferenceImage($media), '::postRemove() remove the original file');
    }
}