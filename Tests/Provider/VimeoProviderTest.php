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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\VimeoProvider;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class VimeoProviderTest extends \PHPUnit_Framework_TestCase
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

        $cdn = new \Sonata\MediaBundle\CDN\Server('/updoads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = new FormatThumbnail;

        $provider = new VimeoProvider('file', $filesystem, $cdn, $generator, $thumbnail);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('Blinky™');
        $media->setProviderName('vimeo');
        $media->setProviderReference('21216091');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Vimeo","provider_url":"http:\/\/vimeo.com\/","title":"Blinky\u2122","author_name":"Ruairi Robinson","author_url":"http:\/\/vimeo.com\/ruairirobinson","is_plus":"1","html":"<iframe src=\"http:\/\/player.vimeo.com\/video\/21216091\" width=\"1920\" height=\"1080\" frameborder=\"0\"><\/iframe>","width":"1920","height":"1080","duration":"771","description":"","thumbnail_url":"http:\/\/b.vimeocdn.com\/ts\/136\/375\/136375440_1280.jpg","thumbnail_width":1280,"thumbnail_height":720,"video_id":"21216091"}', true));

        $media->setId(1023457);
        $this->assertEquals('http://b.vimeocdn.com/ts/136/375/136375440_1280.jpg', $provider->getReferenceImage($media));

        $this->assertEquals('default/0011/24', $provider->generatePath($media));
        $this->assertEquals('/updoads/media/default/0011/24/thumb_1023457_big.jpg', $provider->generatePublicUrl($media, 'big'));
    }

    public function testThumbnail()
    {
        $provider = $this->getProvider();

        $media = new Media;
        $media->setName('Blinky™');
        $media->setProviderName('vimeo');
        $media->setProviderReference('21216091');
        $media->setProviderMetadata(json_decode('{"type":"video","version":"1.0","provider_name":"Vimeo","provider_url":"http:\/\/vimeo.com\/","title":"Blinky\u2122","author_name":"Ruairi Robinson","author_url":"http:\/\/vimeo.com\/ruairirobinson","is_plus":"1","html":"<iframe src=\"http:\/\/player.vimeo.com\/video\/21216091\" width=\"1920\" height=\"1080\" frameborder=\"0\"><\/iframe>","width":"1920","height":"1080","duration":"771","description":"","thumbnail_url":"http:\/\/b.vimeocdn.com\/ts\/136\/375\/136375440_1280.jpg","thumbnail_width":1280,"thumbnail_height":720,"video_id":"21216091"}', true));

        $media->setId(1023457);

        $this->assertTrue($provider->requireThumbnails($media));

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $this->assertNotEmpty($provider->getFormats(), '::getFormats() return an array');

        $provider->generateThumbnails($media);

        $this->assertEquals('default/0011/24/thumb_1023457_big.jpg', $provider->generatePrivateUrl($media, 'big'));
    }

    public function testEvent()
    {
        $provider = $this->getProvider();

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $media = new Media;
        $media->setBinaryContent('BDYAbAtaDzA');
        $media->setId(1023456);

        stream_wrapper_unregister('http');
        stream_wrapper_register('http', 'Sonata\\MediaBundle\\Tests\\Provider\\FakeHttpWrapper');

        // pre persist the media
        $provider->prePersist($media);

        $this->assertEquals('Blinky™', $media->getName(), '::getName() return the file name');
        $this->assertEquals('BDYAbAtaDzA', $media->getProviderReference(), '::getProviderReference() is set');

        // post persit the media
        $provider->postPersist($media);

        $provider->postRemove($media);

        $media->setProviderStatus('fake');
        $provider->preUpdate($media);

        $this->assertEquals(MediaInterface::STATUS_OK, $media->getProviderStatus());
        $provider->postUpdate($media);

        $media->setProviderStatus('fake');
        $media->setBinaryContent(null);

        $provider->prePersist($media);
        $this->assertEquals('fake', $media->getProviderStatus());

        $provider->preUpdate($media);
        $this->assertEquals('fake', $media->getProviderStatus());

        $provider->postPersist($media);
        $this->assertEquals('fake', $media->getProviderStatus());

        $provider->preRemove($media);

        stream_wrapper_restore('http');
    }

    public function testForm()
    {
        if (!class_exists('\Sonata\AdminBundle\Form\FormMapper')) {
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

        $properties = $provider->getHelperProperties($media, 'admin', array('width' => 150));
        $this->assertEquals(150, $properties['width']);
    }
}