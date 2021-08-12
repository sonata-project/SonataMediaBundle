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
use Psr\Log\LoggerInterface;
use Sonata\MediaBundle\Form\DataTransformer\ServiceProviderDataTransformer;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

class ServiceProviderDataTransformerTest extends TestCase
{
    public function testTransformNoop(): void
    {
        $transformer = new ServiceProviderDataTransformer(
            $this->createStub(MediaProviderInterface::class)
        );

        $value = new \stdClass();
        self::assertSame($value, $transformer->transform($value));
    }

    public function testReverseTransformSkipsProviderIfNotMedia(): void
    {
        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::never())->method('transform');

        $transformer = new ServiceProviderDataTransformer($provider);

        $media = new \stdClass();
        self::assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformForwardsToProvider(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('transform')->with($media);

        $transformer = new ServiceProviderDataTransformer($provider);
        self::assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithThrowingProviderNoThrow(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('transform')->with($media)->willThrowException(new \Exception());

        $transformer = new ServiceProviderDataTransformer($provider);
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithThrowingProviderLogsException(): void
    {
        $media = $this->createStub(MediaInterface::class);

        $exception = new \Exception('foo');
        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects(self::once())->method('transform')->with($media)->willThrowException($exception);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            self::stringStartsWith('Caught Exception Exception: "foo" at'),
            ['exception' => $exception]
        );

        $transformer = new ServiceProviderDataTransformer($provider);
        $transformer->setLogger($logger);
        $transformer->reverseTransform($media);
    }
}
