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
        $resizer = $this->createMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));

        $adapter = $this->getMockBuilder('Sonata\MediaBundle\Filesystem\Local')->disableOriginalConstructor()->getMock();
        $adapter->expects($this->any())->method('getDirectory')->will($this->returnValue(realpath(__DIR__).'/../fixtures'));

        $filesystem = $this->getMockBuilder('Gaufrette\Filesystem')
            ->setMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder('Gaufrette\File')
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->createMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $provider = new FileProvider('file', $filesystem, $cdn, $generator, $thumbnail, ['txt'], ['foo/bar'], $metadata);
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

        $provider->addFormat('admin', ['width' => 100]);
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
        $provider = $this->getProvider();

        $admin = $this->createMock('Sonata\AdminBundle\Admin\AdminInterface');
        $admin->expects($this->any())
            ->method('trans')
            ->will($this->returnValue('message'));

        $formMapper = $this->getMockBuilder('Sonata\AdminBundle\Form\FormMapper')
            ->setMethods(['add', 'getAdmin'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

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
    public function testTransform($expected, $media)
    {
        $self = $this;

        $closure = function () use ($self, $expected, $media) {
            $provider = $self->getProvider();

            $provider->transform($media);

            $self->assertInstanceOf($expected, $media->getBinaryContent());
        };

        $closure();
    }

    public function mediaProvider()
    {
        $file = new \Symfony\Component\HttpFoundation\File\File(realpath(__DIR__.'/../fixtures/file.txt'));
        $content = file_get_contents(realpath(__DIR__.'/../fixtures/file.txt'));
        $request = new Request([], [], [], [], [], [], $content);

        $media1 = new Media();
        $media1->setBinaryContent($file);
        $media1->setContentType('foo');
        $media1->setId(1023456);

        $media2 = new Media();
        $media2->setBinaryContent($request);
        $media2->setContentType('text/plain');
        $media2->setId(1023456);

        return [
            ['\Symfony\Component\HttpFoundation\File\File', $media1],
            ['\Symfony\Component\HttpFoundation\File\File', $media1],
            ['\Symfony\Component\HttpFoundation\File\File', $media2],
        ];
    }

    /**
     * @requires PHP 5.6
     *
     * @see https://github.com/sebastianbergmann/phpunit/issues/1409
     */
    public function testBinaryContentWithRealPath()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');

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
            ->setMethods(['getRealPath', 'getPathname'])
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
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');

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
            ->setMethods(['getRealPath', 'getPathname'])
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

    public function testValidate()
    {
        $errorElement = $this->getMockBuilder('Sonata\CoreBundle\Validator\ErrorElement')
            ->disableOriginalConstructor()
            ->getMock();

        $media = new Media();

        $provider = $this->getProvider();
        $provider->validate($errorElement, $media);
    }

    public function testValidateUploadSize()
    {
        $errorElement = $this->getMockBuilder('Sonata\CoreBundle\Validator\ErrorElement')
            ->disableOriginalConstructor()
            ->getMock();
        $errorElement->expects($this->once())->method('with')
            ->will($this->returnSelf());
        $errorElement->expects($this->once())->method('addViolation')
            ->with($this->stringContains('The file is too big, max size:'))
            ->will($this->returnSelf());
        $errorElement->expects($this->once())->method('end')
            ->will($this->returnSelf());

        $upload = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->expects($this->any())->method('getClientSize')
            ->will($this->returnValue(0));
        $upload->expects($this->any())->method('getFilename')
            ->will($this->returnValue('test.txt'));
        $upload->expects($this->any())->method('getClientOriginalName')
            ->will($this->returnValue('test.txt'));
        $upload->expects($this->any())->method('getMimeType')
            ->will($this->returnValue('foo/bar'));

        $media = new Media();
        $media->setBinaryContent($upload);

        $provider = $this->getProvider();
        $provider->validate($errorElement, $media);
    }

    public function testValidateUploadType()
    {
        $errorElement = $this->getMockBuilder('Sonata\CoreBundle\Validator\ErrorElement')
            ->disableOriginalConstructor()
            ->getMock();
        $errorElement->expects($this->once())->method('with')
            ->will($this->returnSelf());
        $errorElement->expects($this->once())->method('addViolation')
            ->with($this->stringContains('Invalid mime type : %type%', ['type' => 'bar/baz']))
            ->will($this->returnSelf());
        $errorElement->expects($this->once())->method('end')
            ->will($this->returnSelf());

        $upload = $this->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->expects($this->any())->method('getClientSize')
            ->will($this->returnValue(23));
        $upload->expects($this->any())->method('getFilename')
            ->will($this->returnValue('test.txt'));
        $upload->expects($this->any())->method('getClientOriginalName')
            ->will($this->returnValue('test.txt'));
        $upload->expects($this->any())->method('getMimeType')
            ->will($this->returnValue('bar/baz'));

        $media = new Media();
        $media->setBinaryContent($upload);

        $provider = $this->getProvider();
        $provider->validate($errorElement, $media);
    }
}
