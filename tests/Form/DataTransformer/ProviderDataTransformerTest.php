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

namespace Sonata\MediaBundle\Tests\Form\DataTransformer;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProviderDataTransformerTest extends TestCase
{
    public function testReverseTransformFakeValue(): void
    {
        $pool = new Pool('default');

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class);
        static::assertNull($transformer->reverseTransform(null));
    }

    public function testReverseTransformUnknownProvider(): void
    {
        $this->expectException(\RuntimeException::class);

        $pool = new Pool('default');

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::exactly(3))->method('getProviderName')->willReturn('unknown');
        $media->method('getId')->willReturn(1);
        $media->method('getBinaryContent')->willReturn('xcs');

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class, [
            'new_on_update' => false,
        ]);
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformValidProvider(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(static::once())->method('transform');

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::exactly(3))->method('getProviderName')->willReturn('default');
        $media->method('getId')->willReturn(1);
        $media->method('getBinaryContent')->willReturn('xcs');

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class, [
            'new_on_update' => false,
        ]);
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithNewMediaAndNoBinaryContent(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(null);
        $media->method('getBinaryContent')->willReturn(null);
        $media->method('getProviderName')->willReturn('default');
        $media->expects(static::once())->method('setProviderReference')->with(MediaInterface::MISSING_BINARY_REFERENCE);
        $media->expects(static::once())->method('setProviderStatus')->with(MediaInterface::STATUS_PENDING);

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class, [
            'new_on_update' => false,
            'empty_on_new' => false,
        ]);
        static::assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithMediaAndNoBinaryContent(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(1);
        $media->method('getBinaryContent')->willReturn(null);

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class);
        static::assertSame($media, $transformer->reverseTransform($media));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testReverseTransformWithMediaAndUploadFileInstance(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->createMock(MediaInterface::class);
        $media->method('getProviderName')->willReturn('default');
        $media->method('getId')->willReturn(1);
        $media->method('getBinaryContent')->willReturn(new UploadedFile(__FILE__, 'ProviderDataTransformerTest'));

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class, [
            'new_on_update' => false,
        ]);
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithThrowingProviderThrowTransformationFailedException(): void
    {
        $this->expectException(TransformationFailedException::class);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(static::once())->method('transform')->will(static::throwException(new \Exception()));

        $pool = new Pool('default');
        $pool->addProvider('default', $provider);

        $media = $this->createMock(MediaInterface::class);
        $media->expects(static::exactly(3))->method('getProviderName')->willReturn('default');
        $media->method('getId')->willReturn(1);
        $media->method('getBinaryContent')->willReturn(new UploadedFile(__FILE__, 'ProviderDataTransformerTest'));

        $transformer = new ProviderDataTransformer($pool, MediaInterface::class, [
            'new_on_update' => false,
        ]);
        $transformer->reverseTransform($media);
    }
}
