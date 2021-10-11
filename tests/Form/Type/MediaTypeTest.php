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

namespace Sonata\MediaBundle\Tests\Form\Type;

use Sonata\MediaBundle\Form\Type\MediaType;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @author Virgile Vivier <virgilevivier@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 */
class MediaTypeTest extends AbstractTypeTest
{
    protected Pool $mediaPool;

    protected MediaType $mediaType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mediaPool = new Pool('default');
        $this->mediaType = new MediaType($this->mediaPool, Media::class);

        $this->mediaPool->addProvider('provider_a', $this->createStub(MediaProviderInterface::class));
        $this->mediaPool->addProvider('provider_b', $this->createStub(MediaProviderInterface::class));
        $this->mediaPool->addContext('video');
        $this->mediaPool->addContext('pic');

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType($this->mediaType)
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    public function testMissingFormOptions(): void
    {
        $this->expectException(MissingOptionsException::class);
        $this->expectExceptionMessage(
            'The required options "context", "provider" are missing.'
        );

        $this->factory->create($this->getFormType(), null);
    }

    /**
     * @requires extension gd
     */
    public function testMissingFormContextOption(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_a',
        ]);
    }

    public function testMissingFormProviderOption(): void
    {
        $this->expectException(MissingOptionsException::class);

        $this->factory->create($this->getFormType(), null, [
            'context' => 'pic',
        ]);
    }

    public function testInvalidFormProviderOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(
            'The option "provider" with value "provider_c" is invalid. Accepted values are: "provider_a", "provider_b".'
        );

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_c',
            'context' => 'pic',
        ]);
    }

    /**
     * @requires extension gd
     */
    public function testInvalidFormContextOption(): void
    {
        $this->expectException(InvalidOptionsException::class);
        $this->expectExceptionMessage(
            'The option "context" with value "photo" is invalid. Accepted values are: "video", "pic".'
        );

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_b',
            'context' => 'photo',
        ]);
    }

    protected function getTestedInstance(): FormTypeInterface
    {
        return new MediaType($this->mediaPool, Media::class);
    }

    /**
     * @phpstan-return class-string<FormTypeInterface>
     */
    private function getFormType(): string
    {
        return MediaType::class;
    }
}
