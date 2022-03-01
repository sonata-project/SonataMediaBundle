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
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Translator\LabelTranslatorStrategyInterface;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 *
 * @phpstan-template T of MediaProviderInterface
 */
abstract class AbstractProviderTest extends TestCase
{
    /**
     * @var MockObject&FormBuilderInterface
     */
    protected MockObject $formBuilder;

    /**
     * @var FormMapper<MediaInterface>
     */
    protected FormMapper $form;

    /**
     * @phpstan-var T
     */
    protected MediaProviderInterface $provider;

    protected function setUp(): void
    {
        $this->formBuilder = $this->createMock(FormBuilderInterface::class);
        $this->formBuilder->method('getOption')->willReturn('api');

        $admin = new MediaAdmin(new Pool('default'));
        $admin->setModelClass(Media::class);
        $admin->setLabelTranslatorStrategy($this->createStub(LabelTranslatorStrategyInterface::class));
        $admin->setFieldDescriptionFactory($this->createStub(FieldDescriptionFactoryInterface::class));

        $this->form = new FormMapper(
            $this->createStub(FormContractorInterface::class),
            $this->formBuilder,
            $admin
        );

        $this->provider = $this->getProvider();
    }

    /**
     * Get the provider which have to be tested.
     *
     * @phpstan-return T
     */
    abstract public function getProvider(): MediaProviderInterface;

    public function testBuildEditForm(): void
    {
        $this->formBuilder
            ->expects(static::atLeastOnce())
            ->method('add');

        $this->provider->buildEditForm($this->form);
    }

    public function testBuildCreateForm(): void
    {
        $this->formBuilder
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
