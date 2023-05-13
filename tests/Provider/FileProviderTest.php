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
use Sonata\Form\Twig\CanonicalizeRuntime;
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
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @phpstan-extends AbstractProviderTest<FileProvider>
 */
class FileProviderTest extends AbstractProviderTest
{
    public function getProvider(): MediaProviderInterface
    {
        $resizer = $this->createStub(ResizerInterface::class);
        $thumbnail = $this->createStub(ThumbnailInterface::class);
        $metadata = $this->createStub(MetadataBuilderInterface::class);

        $adapter = new Local(realpath(__DIR__).'/../Fixtures');
        $cdn = new Server('/uploads/media');
        $generator = new IdGenerator();

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(GaufretteFile::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();

        $file->method('getName')->willReturn('name');
        $filesystem->method('get')->willReturn($file);
        $thumbnail->method('generatePublicUrl')->willReturn('/bundles/sonatamedia/file.png');

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
        $media->setId(1_023_456);

        static::assertSame('default/0011/24/ASDASD.txt', $this->provider->getReferenceImage($media));
        static::assertSame('default/0011/24', $this->provider->generatePath($media));
        static::assertSame('/bundles/sonatamedia/file.png', $this->provider->generatePublicUrl($media, 'admin'));
        static::assertSame('/uploads/media/default/0011/24/ASDASD.txt', $this->provider->generatePublicUrl($media, 'reference'));
    }

    public function testHelperProperties(): void
    {
        $this->provider->addFormat('admin', [
            'width' => 100,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
        $media = new Media();
        $media->setName('test.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setContext('default');
        $media->setId(10);
        $media->setHeight(100);

        $properties = $this->provider->getHelperProperties($media, 'admin');

        static::assertSame('test.png', $properties['title']);
    }

    public function testForm(): void
    {
        $this->formBuilder->expects(static::exactly(8))
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
        $media->setProviderReference('ASDASDAS.png');
        $media->setContext('default');
        $media->setId(1_023_456);

        $this->provider->generateThumbnails($media);
    }

    public function testEvent(): void
    {
        $this->provider->addFormat('big', [
            'width' => 200,
            'height' => 100,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        $file = __DIR__.'/../Fixtures/file.txt';

        $media = new Media();
        $media->setId(123);
        $media->setContext('default');

        $this->provider->preUpdate($media);
        static::assertNull($media->getProviderReference());

        $media->setBinaryContent($file);
        $this->provider->transform($media);

        static::assertInstanceOf(\DateTimeInterface::class, $media->getUpdatedAt());
        static::assertNotNull($media->getProviderReference());

        $this->provider->postUpdate($media);

        $realPath = realpath(__DIR__.'/../Fixtures/file.txt');

        static::assertNotFalse($realPath);

        $file = new File($realPath);

        $media = new Media();
        $media->setContext('default');
        $media->setBinaryContent($file);
        $media->setId(1_023_456);

        // pre persist the media
        $this->provider->transform($media);

        static::assertSame('file.txt', $media->getName(), '::getName() return the file name');
        static::assertNotNull($media->getProviderReference(), '::getProviderReference() is set');
        static::assertNotNull($this->provider->generatePrivateUrl($media, 'reference'), '::generatePrivateUrl() return path for reference formate');

        $this->provider->generatePrivateUrl($media, 'big');
    }

    public function testDownload(): void
    {
        $realPath = realpath(__DIR__.'/../Fixtures/FileProviderTest/0011/24/file.txt');

        static::assertNotFalse($realPath);

        $file = new File($realPath);

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setProviderReference('file.txt');
        $media->setContext('FileProviderTest');
        $media->setId(1_023_456);

        $response = $this->provider->getDownloadResponse($media, 'reference', 'X-Accel-Redirect');

        static::assertInstanceOf(BinaryFileResponse::class, $response);
    }

    /**
     * @dataProvider mediaProvider
     *
     * @phpstan-param class-string $expected
     */
    public function testTransform(string $expected, MediaInterface $media): void
    {
        $closure = function () use ($expected, $media): void {
            $this->provider->transform($media);
            self::assertInstanceOf($expected, $media->getBinaryContent());
        };

        $closure();
    }

    /**
     * @phpstan-return iterable<array{class-string, MediaInterface}>
     */
    public function mediaProvider(): iterable
    {
        $realPath = realpath(__DIR__.'/../Fixtures/file.txt');

        static::assertNotFalse($realPath);

        $file = new File($realPath);

        $content = file_get_contents($realPath);

        static::assertNotFalse($content);

        $media = new Media();
        $media->setBinaryContent($file);
        $media->setContentType('foo');
        $media->setId(1_023_456);

        yield [File::class, $media];
        yield [File::class, $media];
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

        $binaryContent->expects(static::atLeastOnce())
            ->method('getRealPath')
            ->willReturn(__DIR__.'/../Fixtures/file.txt');

        $binaryContent->expects(static::never())
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

        $binaryContent->expects(static::atLeastOnce())
            ->method('getRealPath')
            ->willReturn(false);

        $binaryContent->expects(static::atLeastOnce())
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
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $errorElement = $this->createErrorElement($executionContext);

        $media = new Media();

        $this->provider->validate($errorElement, $media);
    }

    public function testValidateUploadSize(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects(static::once())
            ->method('buildViolation')
            ->with(static::stringContains('The file is too big, max size:'))
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
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects(static::once())
            ->method('buildViolation')
            ->with(static::stringContains('The file is too big, max size:'))
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

    public function testValidateUploadSizeOK(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $errorElement = $this->createErrorElement($executionContext);
        $executionContext
            ->expects(static::never())
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
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $errorElement = $this->createErrorElement($executionContext);
        $constraintBuilder = $this->createConstraintBuilder();
        $constraintBuilder
            ->expects(static::once())
            ->method('setParameters')
            ->with(['%type%' => 'bar/baz']);
        $executionContext
            ->expects(static::once())
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
        static::assertSame('file', $this->provider->getProviderMetadata()->getTitle());
        static::assertSame('file.description', $this->provider->getProviderMetadata()->getDescription());
        static::assertNotNull($this->provider->getProviderMetadata()->getImage());
        static::assertSame('fa fa-file-text-o', $this->provider->getProviderMetadata()->getOption('class'));
        static::assertSame('SonataMediaBundle', $this->provider->getProviderMetadata()->getDomain());
    }

    private function createErrorElement(ExecutionContextInterface $executionContext): ErrorElement
    {
        // TODO: Remove if when dropping support for `sonata-project/form-extensions` 2.0.
        if (class_exists(CanonicalizeRuntime::class)) {
            return new ErrorElement(
                '',
                $this->createStub(ConstraintValidatorFactoryInterface::class),
                $executionContext,
                'group'
            );
        }

        // @phpstan-ignore-next-line
        return new ErrorElement(
            '',
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
