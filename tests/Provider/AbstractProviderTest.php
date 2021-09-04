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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 */
abstract class AbstractProviderTest extends TestCase
{
    /**
     * @var FormBuilder|MockObject
     */
    protected $formBuilder;

    /**
     * @var FormMapper|MockObject
     */
    protected $form;

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
        $this->form = $this->createMock(FormMapper::class);

        $this->formBuilder = $this->createMock(FormBuilder::class);
        $this->formBuilder->method('getOption')->willReturn('api');

        $this->provider = $this->getProvider();
    }

    /**
     * Get the provider which have to be tested.
     */
    abstract public function getProvider(): MediaProviderInterface;

    public function testBuildEditForm(): void
    {
        $this->form
            ->expects(static::atLeastOnce())
            ->method('add');

        $this->provider->buildEditForm($this->form);
    }

    public function testBuildCreateForm(): void
    {
        $this->form
            ->expects(static::atLeastOnce())
            ->method('add');

        $this->provider->buildCreateForm($this->form);
    }

    public function testBuildMediaType(): void
    {
        $this->formBuilder
            ->expects(static::atLeastOnce())
            ->method('add');

        $this->provider->buildMediaType($this->formBuilder);
    }

    final protected function createResponse(string $content): ResponseInterface
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(static::once())->method('getBody')->willReturn($stream);

        return $response;
    }
}
