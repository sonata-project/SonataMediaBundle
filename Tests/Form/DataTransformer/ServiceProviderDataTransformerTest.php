<?php

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
use Sonata\MediaBundle\Form\DataTransformer\ServiceProviderDataTransformer;

class ServiceProviderDataTransformerTest extends TestCase
{
    public function testTransformNoop()
    {
        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');

        $transformer = new ServiceProviderDataTransformer($provider->reveal());

        $value = new \stdClass();
        $this->assertSame($value, $transformer->transform($value));
    }

    public function testReverseTransformSkipsProviderIfNotMedia()
    {
        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->transform()->shouldNotBeCalled();

        $transformer = new ServiceProviderDataTransformer($provider->reveal());

        $media = new \stdClass();
        $this->assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformForwardsToProvider()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\MediaInterface')->reveal();

        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->transform(Argument::is($media))->shouldBeCalledTimes(1);

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $this->assertSame($media, $transformer->reverseTransform($media));
    }

    public function testReverseTransformWithThrowingProviderNoThrow()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\MediaInterface')->reveal();

        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->transform(Argument::is($media))->shouldBeCalled()->willThrow(new \Exception());

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $transformer->reverseTransform($media);
    }

    public function testReverseTransformWithThrowingProviderLogsException()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\MediaInterface')->reveal();

        $exception = new \Exception('foo');
        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->transform(Argument::is($media))->shouldBeCalled()->willThrow($exception);

        $logger = $this->prophesize('Psr\Log\LoggerInterface');
        $logger->error(
            Argument::containingString('Caught Exception Exception: "foo" at'),
            Argument::is(['exception' => $exception])
        )->shouldBeCalled();

        $transformer = new ServiceProviderDataTransformer($provider->reveal());
        $transformer->setLogger($logger->reveal());
        $transformer->reverseTransform($media);
    }
}
