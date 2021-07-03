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
    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addType($this->formType)
            ->addExtensions($this->getExtensions())
            ->getFormFactory();
    }

    public function testMissingFormOptions(): void
    {
        $this->mediaPool->method('getProviderList')->willReturn([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]);
        $this->mediaPool->method('getContexts')->willReturn([
            'video' => [],
            'pic' => [],
        ]);

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
        $this->mediaPool->method('getProviderList')->willReturn([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]);
        $this->mediaPool->method('getContexts')->willReturn([
            'video' => [],
            'pic' => [],
        ]);

        $this->expectException(MissingOptionsException::class);

        $this->factory->create($this->getFormType(), null, [
            'provider' => 'provider_a',
        ]);
    }

    public function testMissingFormProviderOption(): void
    {
        $this->mediaPool->method('getProviderList')->willReturn([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]);
        $this->mediaPool->method('getContexts')->willReturn([
            'video' => [],
            'pic' => [],
        ]);

        $this->expectException(MissingOptionsException::class);

        $this->factory->create($this->getFormType(), null, [
            'context' => 'pic',
        ]);
    }

    public function testInvalidFormProviderOption(): void
    {
        $this->mediaPool->method('getProviderList')->willReturn([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]);
        $this->mediaPool->method('getContexts')->willReturn([
            'video' => [],
            'pic' => [],
        ]);

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
        $this->mediaPool->method('getProviderList')->willReturn([
            'provider_a' => 'provider_a',
            'provider_b' => 'provider_b',
        ]);
        $this->mediaPool->method('getContexts')->willReturn([
            'video' => [],
            'pic' => [],
        ]);

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
        return new MediaType($this->mediaPool, 'testclass');
    }

    /**
     * @phpstan-return class-string<\Symfony\Component\Form\FormTypeInterface>
     */
    private function getFormType(): string
    {
        return MediaType::class;
    }
}
