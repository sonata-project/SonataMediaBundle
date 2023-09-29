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

namespace Sonata\MediaBundle\Tests\Resizer;

use Gaufrette\File;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\CropResizer;

/**
 * @author Christian Gripp <mail@core23.de>
 */
final class CropResizerTest extends TestCase
{
    private const FORMAT = 'format';

    private const QUALITY = 75;

    /**
     * @var MockObject&ImagineInterface
     */
    private MockObject $adapter;

    /**
     * @var Stub&MetadataBuilderInterface
     */
    private Stub $metadata;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adapter = $this->createMock(ImagineInterface::class);
        $this->metadata = $this->createStub(MetadataBuilderInterface::class);
    }

    /**
     * @dataProvider provideResizeCases
     */
    public function testResize(
        int $srcWidth,
        int $srcHeight,
        int $targetWidth,
        int $targetHeight,
        int $scaleWidth,
        int $scaleHeight,
        int $cropWidth,
        int $cropHeight
    ): void {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getContext')->willReturn('sample');
        $media->method('getProviderName')->willReturn('acme.sample.provider');
        $media->method('getBox')->willReturn(new Box($srcWidth, $srcHeight));

        $input = $this->createStub(File::class);
        $output = $this->createStub(File::class);
        $output->method('getName')->willReturn('output');

        $image = $this->createMock(ImageInterface::class);
        $image->expects(0 === $scaleHeight && 0 === $scaleWidth ? static::never() : static::once())
            ->method('thumbnail')
            ->with(
                static::callback(static fn (Box $box): bool => $box->getWidth() === $scaleWidth && $box->getHeight() === $scaleHeight),
                static::equalTo('outbound')
            )
            ->willReturnReference($image);

        $image->expects(0 === $cropWidth && 0 === $cropHeight ? static::never() : static::once())
            ->method('crop')
            ->with(
                static::anything(),
                static::callback(static fn (Box $box): bool => $box->getWidth() === $cropWidth && $box->getHeight() === $cropHeight)
            )
            ->willReturnReference($image);

        $image->method('get')
            ->with(self::FORMAT, [
                'quality' => self::QUALITY,
            ])
            ->willReturn('CONTENT');

        $this->adapter->method('load')->willReturn($image);

        $resizer = new CropResizer($this->adapter, $this->metadata);
        $resizer->resize($media, $input, $output, self::FORMAT, [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'quality' => self::QUALITY,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
    }

    /**
     * @phpstan-return iterable<array{int, int, int, int, int, int, int, int}>
     */
    public function provideResizeCases(): iterable
    {
        yield 'landscape: resize, no crop' => [800, 200, 400, 100, 400, 100, 0, 0];
        yield 'landscape: resize, crop' => [800, 200, 600, 100, 600, 150, 600, 100];
        yield 'landscape: no resize, crop' => [800, 200, 800, 100, 0, 0, 800, 100];

        yield 'landscape to portrait: no resize, crop' => [800, 200, 200, 800, 0, 0, 200, 200];
        yield 'landscape to portrait: resize, crop' => [8000, 4000, 400, 800, 1600, 800, 400, 800];

        yield 'portrait: resize, no crop' => [200, 800, 100, 400, 100, 400, 0, 0];
        yield 'portrait: resize, crop' => [200, 800, 100, 600, 150, 600, 100, 600];
        yield 'portrait: no resize, crop' => [200, 800, 100, 800, 0, 0, 100, 800];

        yield 'portrait to landscape: crop' => [200, 800, 800, 200, 0, 0, 200, 200];
        yield 'portrait to landscape: resize, crop' => [4000, 8000, 800, 400, 800, 1600, 800, 400];

        yield 'square: resize, no crop' => [200, 200, 100, 100, 100, 100, 0, 0];
        yield 'square: no resize, no crop' => [200, 200, 200, 200, 0, 0, 0, 0];
    }

    /**
     * @dataProvider provideResizeNoChangeCases
     */
    public function testResizeNoChange(
        int $srcWidth,
        int $srcHeight,
        int $targetWidth,
        int $targetHeight
    ): void {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getContext')->willReturn('sample');
        $media->method('getProviderName')->willReturn('acme.sample.provider');
        $media->method('getBox')->willReturn(new Box($srcWidth, $srcHeight));

        $input = $this->createStub(File::class);
        $output = $this->createStub(File::class);
        $output->method('getName')->willReturn('output');

        $image = $this->createMock(ImageInterface::class);
        $image->expects(static::never())->method('thumbnail');
        $image->expects(static::never())->method('crop');

        $image->method('get')
            ->with(self::FORMAT, [
                'quality' => self::QUALITY,
            ])
            ->willReturn('CONTENT');

        $this->adapter->method('load')->willReturn($image);

        $resizer = new CropResizer($this->adapter, $this->metadata);
        $resizer->resize($media, $input, $output, self::FORMAT, [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'quality' => self::QUALITY,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);
    }

    /**
     * @phpstan-return iterable<array{int, int, int, int}>
     */
    public function provideResizeNoChangeCases(): iterable
    {
        yield 'landscape: match' => [800, 200, 800, 200];
        yield 'landscape: small width' => [800, 100, 800, 200];
        yield 'landscape: small height' => [700, 200, 800, 200];

        yield 'portrait: match' => [200, 800, 200, 800];
        yield 'portrait: small width' => [100, 800, 200, 800];
        yield 'portrait: small height' => [200, 700, 200, 800];

        yield 'square: match' => [200, 200, 200, 200];
        yield 'square: small' => [100, 100, 200, 200];
    }

    /**
     * @dataProvider provideGetBoxCases
     */
    public function testGetBox(int $srcWidth, int $srcHeight, int $targetWidth, int $targetHeight, int $expectWidth, int $expectHeight): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getWidth')
            ->willReturn($srcWidth);
        $media->method('getHeight')
            ->willReturn($srcHeight);
        $media->expects(static::once())->method('getBox')
            ->willReturn(new Box($srcWidth, $srcHeight));

        $resizer = new CropResizer($this->adapter, $this->metadata);
        $box = $resizer->getBox($media, [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => true,
            'resizer' => null,
            'resizer_options' => [],
        ]);

        static::assertSame($expectWidth, $box->getWidth(), 'width mismatch');
        static::assertSame($expectHeight, $box->getHeight(), 'height mismatch');
    }

    /**
     * @phpstan-return iterable<array{int, int, int, int, int, int}>
     */
    public function provideGetBoxCases(): iterable
    {
        yield 'source = target' => [800, 800, 800, 800, 800, 800];

        yield 'square: same ratio' => [1000, 1000, 60, 60, 60, 60];
        yield 'square: wrong ratio (width)' => [1000, 1000, 200, 100, 200, 100];
        yield 'square: wrong ratio (height)' => [1000, 1000, 100, 200, 100, 200];
        yield 'square: source too small' => [1000, 1000, 2000, 2000, 1000, 1000];
        yield 'square: source too wide' => [1000, 1000, 2000, 100, 1000, 100];
        yield 'square: source too high' => [1000, 1000, 100, 2000, 100, 1000];

        yield 'landscape: same ratio' => [1000, 100, 600, 60, 600, 60];
        yield 'landscape: wrong ratio (width)' => [1000, 100, 600, 30, 600, 30];
        yield 'landscape: wrong ratio (height)' => [1000, 100, 400, 10, 400, 10];
        yield 'landscape: source too small' => [1000, 100, 10000, 1000, 1000, 100];
        yield 'landscape: source too wide' => [1000, 100, 2000, 100, 1000, 100];
        yield 'landscape: source too high' => [1000, 100, 100, 2000, 100, 100];

        yield 'portrait: same ratio' => [100, 1000, 60, 600, 60, 600];
        yield 'portrait: wrong ratio (width)' => [100, 1000, 30, 600, 30, 600];
        yield 'portrait: wrong ratio (height)' => [100, 1000, 10, 400, 10, 400];
        yield 'portrait: source too small' => [100, 1000, 1000, 10000, 100, 1000];
        yield 'portrait: source too wide' => [100, 1000, 2000, 100, 100, 100];
        yield 'portrait: source too high' => [100, 1000, 100, 2000, 100, 1000];
    }
}
