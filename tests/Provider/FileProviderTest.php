<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

use Gaufrette\File as GaufretteFile;
use Gaufrette\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @phpstan-extends AbstractProviderTest<\Sonata\MediaBundle\Provider\FileProvider>
 */
class FileProviderTest extends AbstractProviderTest
{
    public function getProvider(): MediaProviderInterface
    {
        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->method('resize')->willReturn(true);

        $adapter = $this->createMock(Local::class);
        $adapter->method('getDirectory')->willReturn(realpath(__DIR__).'/../Fixtures');

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(GaufretteFile::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();
        $filesystem->method('get')->willReturn($file);

        $cdn = new Server('/uploads/media');

        $generator = new IdGenerator();

        $thumbnail = new FormatThumbnail('jpg');

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new FileProvider('file', $filesystem, $cdn, $generator, $thumbnail, ['txt'], ['foo/bar'], $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testProvider(): void
    {
        $media = new Media();
        $media->setName('test.txt');
        $media->setProviderReference('ASDASD.txt');
        $media->setContext('default');

        $media->setId(1023456);
        $this->assertSame('default/0011/24/ASDASD.txt', $this->provider->getReferenceImage($media));

        $this->assertSame('default/0011/24', $this->provider->generatePath($media));

        // default icon image
        $this->assertSame('/uploads/media/sonatamedia/files/big/file.png', $this->provider->generatePublicUrl($media, 'big'));
    }

    public function testHelperProperties(): void
    {
        $this->provider->addFormat('admin', ['width' => 100]);
        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(10);
        $media->setHeight(100);

        $properties = $this->provider->getHelperProperties($media, 'admin');

        $this->assertIsArray($properties);
        $this->assertSame('test.png', $properties['title']);
    }

    public function testForm(): void
    {
        $this->formBuilder->expects($this->exactly(8))
            ->method('add');

        $this->provider->buildCreateForm($this->form);
        $this->provider->buildEditForm($this->form);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testThumbnail(): void
    {
        $media = new Media();
        $media->setName('test.png');
        $media->setId(1023456);

        $this->provider->generateThumbnails($media);
    }

    public function testEvent(): void
    {
        $this->provider->addFormat('big', ['width' => 200, 'height' => 100, 'constraint' => true]);

        $file = __DIR__.'/../Fixtures/file.txt';

        $media = new Media();
        $this->provider->preUpdate($media);
        $this->assertNull($media->getProviderReference());

        $media->setBinaryContent($file);
        $this->provider->transform($media);

        $this->assertInstanceOf(\DateTimeInterface::class, $media->getUpdatedAt());
        $this->assertNotNull($media->getProviderReference());

        $this->provider->postUpdate($media);

        $file = new File(realpath(__DIR__.'/../Fixtures/file.txt'));

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent($file);
        $media->setId(1023456);

        // pre persist the media
        $this->provider->transform($media);

        $this->assertSame('file.txt', $media->getName(), '::getName() return the file name');
        $this->assertNotNull($media->getProviderReference(), '::getProviderReference() is set');

        $this->assertEmpty($this->provider->generatePrivateUrl($media, 'big'), '::generatePrivateUrl() return empty on non reference formate');
        $this->assertNotNull($this->provider->generatePrivateUrl($media, 'reference'), '::generatePrivateUrl() return path for reference formate');
    }

    public function testDownload(): void
    {
        $file = new File(realpath(__DIR__.'/../Fixtures/FileProviderTest/0011/24/file.txt'));

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setProviderReference('file.txt');
        $media->setContext('FileProviderTest');
        $media->setId(1023456);

        $response = $this->provider->getDownloadResponse($media, 'reference', 'X-Accel-Redirect');

        $this->assertInstanceOf(BinaryFileResponse::class, $response);
    }

    /**
     * @dataProvider mediaProvider
     */
    public function testTransform(string $expected, Media $media): void
    {
        $closure = function () use ($expected, $media): void {
            $this->provider->transform($media);
            $this->assertInstanceOf($expected, $media->getBinaryContent());
        };

        $closure();
    }

    public function mediaProvider(): array
    {
        $file = new File(realpath(__DIR__.'/../Fixtures/file.txt'));
        $content = file_get_contents(realpath(__DIR__.'/../Fixtures/file.txt'));
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
            [File::class, $media1],
            [File::class, $media1],
            [File::class, $media2],
        ];
    }

    public function testBinaryContentWithRealPath(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $media
            ->method('getProviderReference')
            ->willReturn('provider');

        $media
            ->method('getId')
            ->willReturn(10000);

        $media
            ->method('getContext')
            ->willReturn('context');

        $binaryContent = $this->createMock(File::class);

        $binaryContent->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(__DIR__.'/../Fixtures/file.txt');

        $binaryContent->expects($this->never())
            ->method('getPathname');

        $media
            ->method('getBinaryContent')
            ->willReturn($binaryContent);

        $setFileContents = new \ReflectionMethod(FileProvider::class, 'setFileContents');
        $setFileContents->setAccessible(true);

        $setFileContents->invoke($this->provider, $media);
    }

    public function testBinaryContentStreamWrapped(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $media
            ->method('getProviderReference')
            ->willReturn('provider');

        $media
            ->method('getId')
            ->willReturn(10000);

        $media
            ->method('getContext')
            ->willReturn('context');

        $binaryContent = $this->createMock(File::class);

        $binaryContent->expects($this->atLeastOnce())
            ->method('getRealPath')
            ->willReturn(false);

        $binaryContent->expects($this->atLeastOnce())
            ->method('getPathname')
            ->willReturn(__DIR__.'/../Fixtures/file.txt');

        $media
            ->method('getBinaryContent')
            ->willReturn($binaryContent);

        $setFileContents = new \ReflectionMethod(FileProvider::class, 'setFileContents');
        $setFileContents->setAccessible(true);

        $setFileContents->invoke($this->provider, $media);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testValidate(): void
    {
        $errorElement = $this->createErrorElement($this->createMock(ExecutionContextInterface::class));

        $media = new Media();

        $this->provider->validate($errorElement, $media);
    }

    public function testValidateUploadSize(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->stringContains('The file is too big, max size:'))
            ->willReturn($this->createConstraintBuilder());

        $upload = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->method('getSize')
            ->willReturn(0);
        $upload->method('getFilename')
            ->willReturn('test.txt');
        $upload->method('getClientOriginalName')
            ->willReturn('test.txt');
        $upload->method('getMimeType')
            ->willReturn('foo/bar');

        $media = new Media();
        $media->setBinaryContent($upload);

        $this->provider->validate($errorElement, $media);
    }

    public function testValidateUploadNullSize(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->stringContains('The file is too big, max size:'))
            ->willReturn($this->createConstraintBuilder());

        $upload = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->method('getSize')
            ->willReturn(null);
        $upload->method('getFilename')
            ->willReturn('test.txt');
        $upload->method('getClientOriginalName')
            ->willReturn('test.txt');
        $upload->method('getMimeType')
            ->willReturn('foo/bar');

        $media = new Media();
        $media->setBinaryContent($upload);

        $this->provider->validate($errorElement, $media);
    }

    public function testValidateUploadSizeOK(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects($this->never())
            ->method('buildViolation');

        $upload = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->method('getSize')
            ->willReturn(1);
        $upload->method('getFilename')
            ->willReturn('test.txt');
        $upload->method('getClientOriginalName')
            ->willReturn('test.txt');
        $upload->method('getMimeType')
            ->willReturn('foo/bar');

        $media = new Media();
        $media->setBinaryContent($upload);

        $this->provider->validate($errorElement, $media);
    }

    public function testValidateUploadType(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $errorElement = $this->createErrorElement($executionContext);
        $constraintBuilder = $this->createConstraintBuilder();
        $constraintBuilder
            ->expects($this->once())
            ->method('setParameters')
            ->with(['%type%' => 'bar/baz']);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('Invalid mime type : %type%')
            ->willReturn($constraintBuilder);

        $upload = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();
        $upload->method('getSize')
            ->willReturn(23);
        $upload->method('getFilename')
            ->willReturn('test.txt');
        $upload->method('getClientOriginalName')
            ->willReturn('test.txt');
        $upload->method('getMimeType')
            ->willReturn('bar/baz');

        $media = new Media();
        $media->setBinaryContent($upload);

        $this->provider->validate($errorElement, $media);
    }

    public function testMetadata(): void
    {
        $this->assertSame('file', $this->provider->getProviderMetadata()->getTitle());
        $this->assertSame('file.description', $this->provider->getProviderMetadata()->getDescription());
        $this->assertNotNull($this->provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-file-text-o', $this->provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $this->provider->getProviderMetadata()->getDomain());
    }

    private function createErrorElement(ExecutionContextInterface $executionContext): ErrorElement
    {
        return new ErrorElement(
            '',
            $this->createStub(ConstraintValidatorFactoryInterface::class),
            $executionContext,
            'group'
        );
    }

    /**
     * @return MockObject&ConstraintViolationBuilderInterface
     */
    private function createConstraintBuilder(): object
    {
        $constraintBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $constraintBuilder
            ->method('atPath')
            ->willReturnSelf();
        $constraintBuilder
            ->method('setParameters')
            ->willReturnSelf();
        $constraintBuilder
            ->method('setTranslationDomain')
            ->willReturnSelf();
        $constraintBuilder
            ->method('setInvalidValue')
            ->willReturnSelf();

        return $constraintBuilder;
    }
}
