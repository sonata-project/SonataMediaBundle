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
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Sonata\MediaBundle\Form\DataTransformer\ServiceProviderDataTransformer;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

class ServiceProviderDataTransformerTest extends TestCase
{
    public function testTransformNoop(): void
    {
        $provider = $this->prophesize(MediaProviderInterface::class);

        $transformer = new ServiceProviderDataTransformer($provider->reveal());

        $value = new \stdClass();
        $this->assertSame($value, $transformer->transform($value));
    }

    public function testReverseTransformSkipsProviderIfNotMedia(): void
    {
        $provider = $this->prophesize(MediaProviderInterface::class);
        $provider->transform()->shouldNotBeCalled();

        $transformer = new ServiceProviderDataTransformer($provider->reveal());

        $media = new \stdClass();
        $this->assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformForwardsToProvider(): void
    {
        $media = $this->prophesize(MediaInterface::class)->reveal();

        $provider = $this->prophesize(MediaProviderInterface::class);
        $provider->transform(Argument::is($media))->shouldBeCalledTimes(1);

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $this->assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithThrowingProviderNoThrow(): void
    {
        $media = $this->prophesize(MediaInterface::class)->reveal();

        $provider = $this->prophesize(MediaProviderInterface::class);
        $provider->transform(Argument::is($media))->shouldBeCalled()->willThrow(new \Exception());

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithThrowingProviderLogsException(): void
    {
        $media = $this->prophesize(MediaInterface::class)->reveal();

        $exception = new \Exception('foo');
        $provider = $this->prophesize(MediaProviderInterface::class);
        $provider->transform(Argument::is($media))->shouldBeCalled()->willThrow($exception);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->error(
            Argument::containingString('Caught Exception Exception: "foo" at'),
            Argument::is(['exception' => $exception])
        )->shouldBeCalled();

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $transformer->setLogger($logger->reveal());
        $transformer->reverseTransform($media);
    }
}
