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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 */
abstract class AbstractProviderTest extends TestCase
{
    /**
     * @var FormBuilderInterface|MockObject
     */
    protected $formBuilder;

    /**
     * @var FormMapper
     */
    protected $formMapper;

    /**
     * @var FormTypeInterface|MockObject
     */
    protected $formType;

    /**
     * @var MediaProviderInterface
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->formBuilder->method('getOption')->willReturn('api');

        $this->formMapper = new FormMapper(
            $this->createStub(FormContractorInterface::class),
            $this->formBuilder,
            $this->createStub(AdminInterface::class)
        );

        $this->provider = $this->getProvider();
    }

    /**
     * Get the provider which have to be tested.
     */
    abstract public function getProvider(): MediaProviderInterface;

    public function testBuildEditForm(): void
    {
        $this->formBuilder
            ->expects($this->atLeastOnce())
            ->method('add');

        $this->provider->buildEditForm($this->formMapper);
    }

    public function testBuildCreateForm(): void
    {
        $this->formBuilder
            ->expects($this->atLeastOnce())
            ->method('add');

        $this->provider->buildCreateForm($this->formMapper);
    }

    public function testBuildMediaType(): void
    {
        $this->formBuilder
            ->expects($this->atLeastOnce())
            ->method('add');

        $this->provider->buildMediaType($this->formBuilder);
    }

    final protected function createResponse(string $content): ResponseInterface
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getBody')->willReturn($stream);

        return $response;
    }
}
