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

class ImageProviderTest extends \PHPUnit_Framework_TestCase
{

    public function getProvider()
    {
        $em = 1;
        $settings = array (
            'cdn_enabled'   => true,
            'cdn_path'      => 'http://here.com',
            'private_path'  => '/fake/path',
            'public_path'   => '/updoads/media',
        );

        $resizer = $this->getMock('Sonata\MediaBundle\Media\ResizerInterface', array('resize'));
        $resizer->expects($this->any())
            ->method('resize')
            ->will($this->returnValue(true));

        $provider = new \Sonata\MediaBundle\Provider\ImageProvider('image', $em, $resizer, $settings);

        return $provider;
    }
    

    public function testProvider()
    {

        $provider = $this->getProvider();
        
        $media = new Media;
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);

        $this->assertEquals('/fake/path/0001/01/ASDASDAS.png', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1');

        $media->setId(1023456);
        $this->assertEquals('/fake/path/0011/24/ASDASDAS.png', $provider->getAbsolutePath($media), '::getAbsolutePath() return the correct path - id = 1023456');

        $this->assertEquals('/fake/path/0011/24/ASDASDAS.png', $provider->getReferenceImage($media));

        $this->assertEquals('/fake/path/0011/24', $provider->generatePrivatePath($media));
        $this->assertEquals('/updoads/media/0011/24', $provider->generatePublicPath($media));
        $this->assertEquals('/updoads/media/0011/24/thumb_1023456_big.jpg', $provider->generatePublicUrl($media, 'big'));

    }

    public function testThumbnail()
    {

        $provider = $this->getProvider();

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

        $provider->addFormat('big', array('width' => 200, 'height' => 100,'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertEquals('/fake/path/0011/24/thumb_1023456_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testEvent()
    {

        $provider = $this->getProvider();

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));
        
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

        $provider->postRemove($media);
    }
}