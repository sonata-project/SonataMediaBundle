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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use PHPUnit\Framework\MockObject\MockObject;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Generator\GeneratorInterface;
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
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @phpstan-extends AbstractProviderTestCase<FileProvider>
 */
final class FileProviderTest extends AbstractProviderTestCase
{
    public function getProvider(): MediaProviderInterface
    {
        $resizer = static::createStub(ResizerInterface::class);
        $thumbnail = static::createStub(ThumbnailInterface::class);
        $metadata = static::createStub(MetadataBuilderInterface::class);

        $adapter = new Local(__DIR__.'/../Fixtures');
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

    #[DoesNotPerformAssertions]
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
     * @phpstan-param class-string $expected
     */
    #[DataProvider('provideTransformCases')]
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
    public static function provideTransformCases(): iterable
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

        $setFileContents->invoke($this->provider, $media);
    }

    #[DoesNotPerformAssertions]
    public function testValidateWithoutBinaryContent(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $this->provider->validateMedia($executionContext, new Media());
    }

    // NEXT_MAJOR: remove test
    #[IgnoreDeprecations]
    public function testValidateWithOverwrittenValidateMethodInChildClass(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');

        $provider = new class('foo', $this->createMock(Filesystem::class), $this->createMock(CDNInterface::class), $this->createMock(GeneratorInterface::class), $this->createMock(ThumbnailInterface::class)) extends FileProvider {
            public bool $called = false;

            public function validate(ErrorElement $errorElement, MediaInterface $media): void
            {
                $this->called = true;
                parent::validate($errorElement, $media);
            }
        };

        $provider->validateMedia($executionContext, new Media());

        static::assertTrue($provider->called);
        /** @phpstan-ignore function.alreadyNarrowedType */
        $expectDeprecationMethod = method_exists(self::class, 'expectUserDeprecationMessage') ? 'expectUserDeprecationMessage' : 'expectDeprecationMessage';
        /* @phpstan-ignore staticMethod.notFound, staticMethod.dynamicName */
        self::{$expectDeprecationMethod}('Since sonata-admin/media-bundle 4.19: Overwriting "Sonata\MediaBundle\Provider\BaseProvider::validate()" is deprecated since sonata-admin/media-bundle 4.19. Override "validateMedia()" instead.');
    }

    // NEXT_MAJOR: remove test
    #[IgnoreDeprecations]
    public function testValidateUploadSizeUsingDeprecatedMethod(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');

        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
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

        $this->provider->validate(new ErrorElement($media, $executionContext, null), $media);

        /** @phpstan-ignore function.alreadyNarrowedType */
        $expectDeprecationMethod = method_exists(self::class, 'expectUserDeprecationMessage') ? 'expectUserDeprecationMessage' : 'expectDeprecationMessage';
        /* @phpstan-ignore staticMethod.notFound, staticMethod.dynamicName */
        self::{$expectDeprecationMethod}('Since sonata-admin/media-bundle 4.19: Calling "Sonata\MediaBundle\Provider\BaseProvider::validate()" is deprecated, use "Sonata\MediaBundle\Provider\BaseProvider::validateMedia()" instead.');
    }

    public function testValidateUploadSize(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
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

        $this->provider->validateMedia($executionContext, $media);
    }

    public function testValidateUploadNullSize(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
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

        $this->provider->validateMedia($executionContext, $media);
    }

    public function testValidateUploadSizeOK(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
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

        $this->provider->validateMedia($executionContext, $media);
    }

    public function testValidateUploadType(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->method('getPropertyPath')->willReturn('foo');
        $constraintBuilder = $this->createConstraintBuilder();
        $executionContext
            ->expects(static::once())
            ->method('buildViolation')
            ->with('Invalid mime type : %type%', ['%type%' => 'bar/baz'])
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

        $this->provider->validateMedia($executionContext, $media);
    }

    public function testMetadata(): void
    {
        static::assertSame('file', $this->provider->getProviderMetadata()->getTitle());
        static::assertSame('file.description', $this->provider->getProviderMetadata()->getDescription());
        static::assertNotNull($this->provider->getProviderMetadata()->getImage());
        static::assertSame('fa fa-file-text-o', $this->provider->getProviderMetadata()->getOption('class'));
        static::assertSame('SonataMediaBundle', $this->provider->getProviderMetadata()->getDomain());
    }

    private function createConstraintBuilder(): ConstraintViolationBuilderInterface&MockObject
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
