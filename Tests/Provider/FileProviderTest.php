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

    public function getProvider()
    {
        $resizer = $this->getMock('Sonata\MediaBundle\Media\ResizerInterface', array('resize'));
        $resizer->expects($this->any())
            ->method('resize')
            ->will($this->returnValue(true));

        $adapter = $this->getMock('Gaufrette\Adapter');

        $file = $this->getMock('Gaufrette\File', array(), array($adapter));

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $provider = new \Sonata\MediaBundle\Provider\FileProvider('file', $filesystem, $cdn, $generator);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('test.txt');
        $media->setProviderReference('ASDASD.txt');

        $media->setId(1023456);
        $this->assertEquals('default/0011/24/ASDASD.txt', $provider->getReferenceImage($media));

        $this->assertEquals('default/0011/24', $provider->generatePath($media));

        // default icon image
        $this->assertEquals('/uploads/media/media_bundle/images/files/big/file.png', $provider->generatePublicUrl($media, 'big'));
    }

    public function testHelperProperies()
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', array('width' => 100));
        $media = new Media;
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);

        $properties = $provider->getHelperProperties($media, 'admin');

        $this->assertInternalType('array', $properties);
        $this->assertEquals('test.png', $properties['title']);
    }

    public function testFixBinaryContent()
    {
        $provider = $this->getProvider();

        $file = __DIR__.'/../fixtures/file.txt';

        $media = new Media;
        $media->setBinaryContent($file);
        $provider->fixBinaryContent($media);

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\File\File', $media->getBinaryContent());
    }

    public function testForm()
    {
        if (!class_exists('\Sonata\AdminBundle\Form\FormMapper')) {
            $this->markTestSkipped("AdminBundle doesn't seem to be installed");
        }
        $provider = $this->getProvider();

        $formMapper     = $this->getMock('Sonata\AdminBundle\Form\FormMapper', array('add'), array(), '', false);
        $formMapper->expects($this->exactly(8))
            ->method('add')
            ->will($this->returnValue(null));

        $provider->buildCreateForm($formMapper);
        $provider->buildEditForm($formMapper);
    }

    public function testThumbnail()
    {
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('test.png');
        $media->setId(1023456);

        $provider->generateThumbnails($media);
    }

    public function testEvent()
    {
        $provider = $this->getProvider();

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $file = __DIR__.'/../fixtures/file.txt';

        $media = new Media;
        $provider->preUpdate($media);
        $this->assertNull($media->getProviderReference());

        $media->setBinaryContent($file);
        $provider->fixBinaryContent($media);

        $provider->preUpdate($media);

        $this->assertInstanceOf('\DateTime', $media->getUpdatedAt());
        $this->assertNotNull($media->getProviderReference());

        $provider->postUpdate($media);

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

        $provider->postRemove($media);
    }

    public function testSetFileContents()
    {
        $adapter = new \Gaufrette\Adapter\InMemory(array('testDir'));
        $filesystem = new \Gaufrette\Filesystem($adapter);
        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');
        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();
        $provider = new \Sonata\MediaBundle\Provider\FileProvider('file', $filesystem, $cdn, $generator);

        $media = new Media;
        $media->setId(853);
        $media->setProviderReference(853);

        $provider->setFileContents($media, realpath(__DIR__.'/../fixtures/file.txt'));
        $this->assertEquals('Hello file text!', $provider->getReferenceFile($media)->getContent());


    }
}