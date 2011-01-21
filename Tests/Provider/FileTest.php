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

class FileProviderTest extends \PHPUnit_Framework_TestCase
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


        $provider = new \Sonata\MediaBundle\Provider\FileProvider('file', $em, $settings);


        $media = new Media;
        $media->setName('test.txt');
        $media->setProviderReference('ASDASD.txt');
        $media->setId(10);

        $this->assertEquals('/fake/path/0001/01/ASDASD.txt', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1');

        $media->setId(1023456);
        $this->assertEquals('/fake/path/0011/24/ASDASD.txt', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1023456');

        $this->assertEquals('/fake/path/0011/24/ASDASD.txt', $provider->getReferenceImage($media));

        $this->assertEquals('/fake/path/0011/24', $provider->generatePrivatePath($media));
        $this->assertEquals('/updoads/media/0011/24', $provider->generatePublicPath($media));

        // default icon image
        $this->assertEquals('/media_bundle/images/files/big/file.png', $provider->generatePublicUrl($media, 'big'));

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


        $provider = new \Sonata\MediaBundle\Provider\FileProvider('file', $em, $settings);

        $media = new Media;
        $media->setName('test.png');
        $media->setId(1023456);

        $provider->generateThumbnails($media);

    }

    public function testEvent() {
        $em = 1;
        $settings = array (
            'cdn_enabled'   => true,
            'cdn_path'      => 'http://here.com',
            'private_path'  => sys_get_temp_dir().'/media_bundle_test',
            'public_path'   => '/updoads/media',
        );


        $provider = new \Sonata\MediaBundle\Provider\FileProvider('file', $em, $settings);

        $provider->addFormat('big', array('width' => 200, 'constraint' => true));

        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/file.txt'));

        $media = new Media;
        $media->setBinaryContent($file);
        $media->setId(1023456);

        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('file.txt', $media->getName(), '::getName() return the file name');
        $this->assertNotNull($media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $this->assertFalse($provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return false');
        $this->assertFileExists($provider->getReferenceImage($media), '::postRemove() remove the original file');

        $provider->postRemove($media);

        $this->assertFileNotExists($provider->getReferenceImage($media), '::postRemove() remove the original file');
    }

}