<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

class FileProviderTest extends AbstractProviderTest
{
    public function getProvider()
    {
        $resizer = $this->getMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));

        $adapter = $this->getMockBuilder('Sonata\MediaBundle\Filesystem\Local')->disableOriginalConstructor()->getMock();
        $adapter->expects($this->any())->method('getDirectory')->will($this->returnValue(realpath(__DIR__).'/../fixtures'));

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $file = $this->getMock('Gaufrette\File', array(), array('foo', $filesystem));
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $provider = new FileProvider('file', $filesystem, $cdn, $generator, $thumbnail, array(), array(), $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider()
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('test.txt');
        $media->setProviderReference('ASDASD.txt');
        $media->setContext('default');

        $media->setId(1023456);
        $this->assertSame('default/0011/24/ASDASD.txt', $provider->getReferenceImage($media));

        $this->assertSame('default/0011/24', $provider->generatePath($media));

        // default icon image
        $this->assertSame('/uploads/media/sonatamedia/files/big/file.png', $provider->generatePublicUrl($media, 'big'));
    }

    public function testHelperProperies()
    {
        $provider = $this->getProvider();

        $provider->addFormat('admin', array('width' => 100));
        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);

        $properties = $provider->getHelperProperties($media, 'admin');

        $this->assertInternalType('array', $properties);
        $this->assertSame('test.png', $properties['title']);
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

    public function testThumbnail()
    {
        $provider = $this->getProvider();

        $media = new Media();
        $media->setName('test.png');
        $media->setId(1023456);

        $provider->generateThumbnails($media);
    }

    public function testEvent()
    {
        $provider = $this->getProvider();

        $provider->addFormat('big', array('width' => 200, 'height' => 100, 'constraint' => true));

        $file = __DIR__.'/../fixtures/file.txt';

        $media = new Media();
        $provider->preUpdate($media);
        $this->assertNull($media->getProviderReference());

        $media->setBinaryContent($file);
        $provider->transform($media);

        $this->assertInstanceOf('\DateTime', $media->getUpdatedAt());
        $this->assertNotNull($media->getProviderReference());

        $provider->postUpdate($media);

        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/file.txt'));

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setId(1023456);

        // pre persist the media
        $provider->transform($media);

        $this->assertSame('file.txt', $media->getName(), '::getName() return the file name');
        $this->assertNotNull($media->getProviderReference(), '::getProviderReference() is set');

        $this->assertFalse($provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return false on non reference formate');
        $this->assertNotNull($provider->generatePrivateUrl($media, 'reference'), '::generatePrivateUrl() return path for reference formate');
    }

    public function testDownload()
    {
        $provider = $this->getProvider();

        $file = new File(realpath(__DIR__.'/../fixtures/FileProviderTest/0011/24/file.txt'));

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setProviderReference('file.txt');
        $media->setContext('FileProviderTest');
        $media->setId(1023456);

        $response = $provider->getDownloadResponse($media, 'reference', 'X-Accel-Redirect');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\BinaryFileResponse', $response);
    }

    /**
     * @dataProvider mediaProvider
     */
    public function testTransform($expected, $media, $overridePhpSapiName = true)
    {
        $self = $this;

        $closure = function () use ($self, $expected, $media, $overridePhpSapiName) {
            if ($overridePhpSapiName) {
                require_once 'phpSapiNameOverride.php';
            }

            $provider = $self->getProvider();

            try {
                $provider->transform($media);

                $self->assertInstanceOf($expected, $media->getBinaryContent());
            } catch (\Exception $e) {
                if ($overridePhpSapiName) {
                    $self->assertInstanceOf('\RuntimeException', $e);
                    $self->assertNull($media->getContentType());
                } else {
                    $self->assertEquals('The current process cannot be executed in cli environment', $e->getMessage());
                }
            }
        };

        $closure();
    }

    public function mediaProvider()
    {
        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/file.txt'));
        $content = file_get_contents(realpath(__DIR__.'/../fixtures/file.txt'));
        $request = new Request(array(), array(), array(), array(), array(), array(), $content);

        $media1 = new Media();
        $media1->setBinaryContent($file);
        $media1->setContentType('foo');
        $media1->setId(1023456);

        $media2 = new Media();
        $media2->setBinaryContent($request);
        $media2->setContentType('text/plain');
        $media2->setId(1023456);

        $media3 = new Media();
        $media3->setBinaryContent($request);
        $media3->setId(1023456);

        return array(
            array('\Symfony\Component\HttpFoundation\File\File', $media1, false),
            array('\Symfony\Component\HttpFoundation\File\File', $media1),
            array('\Symfony\Component\HttpFoundation\File\File', $media2),
            array(null, $media3),
        );
    }

    /**
     * @requires PHP 5.6
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1409
     */
    public function testBinaryContentWithRealPath()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $media->expects($this->any())
            ->method('getProviderReference')
            ->willReturn('provider');

        $media->expects($this->any())
            ->method('getId')
            ->willReturn(10000);

        $media->expects($this->any())
            ->method('getContext')
            ->willReturn('context');

        $binaryContent = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->setMethods(array('getRealPath', 'getPathname'))
            ->disableOriginalConstructor()
            ->getMock();

        $binaryContent->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(__DIR__.'/../fixtures/file.txt');

        $binaryContent->expects($this->never())
            ->method('getPathname');

        $media->expects($this->any())
            ->method('getBinaryContent')
            ->willReturn($binaryContent);

        $provider = $this->getProvider();

        $setFileContents = new \ReflectionMethod('Sonata\MediaBundle\Provider\FileProvider', 'setFileContents');
        $setFileContents->setAccessible(true);

        $setFileContents->invoke($provider, $media);
    }

    /**
     * @requires PHP 5.6
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1409
     */
    public function testBinaryContentStreamWrapped()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $media->expects($this->any())
            ->method('getProviderReference')
            ->willReturn('provider');

        $media->expects($this->any())
            ->method('getId')
            ->willReturn(10000);

        $media->expects($this->any())
            ->method('getContext')
            ->willReturn('context');

        $binaryContent = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->setMethods(array('getRealPath', 'getPathname'))
            ->disableOriginalConstructor()
            ->getMock();

        $binaryContent->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(false);

        $binaryContent->expects($this->atLeastOnce())
            ->method('getPathname')
            ->willReturn(__DIR__.'/../fixtures/file.txt');

        $media->expects($this->any())
            ->method('getBinaryContent')
            ->willReturn($binaryContent);

        $provider = $this->getProvider();

        $setFileContents = new \ReflectionMethod('Sonata\MediaBundle\Provider\FileProvider', 'setFileContents');
        $setFileContents->setAccessible(true);

        $setFileContents->invoke($provider, $media);
    }
}
