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

namespace Sonata\MediaBundle\Form\Type;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * NEXT_MAJOR: Remove this file.
 *
 * @author Hugo Briand <briand@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class ApiMediaType extends AbstractType implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Pool
     */
    private $mediaPool;

    /**
     * @var string
     *
     * @phpstan-var class-string<\Sonata\MediaBundle\Model\MediaInterface>
     */
    private $class;

    /**
     * @phpstan-param class-string<\Sonata\MediaBundle\Model\MediaInterface> $class
     */
    public function __construct(Pool $mediaPool, string $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class = $class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataTransformer = new ProviderDataTransformer($this->mediaPool, $this->class, [
            'empty_on_new' => false,
        ]);
        $dataTransformer->setLogger($this->logger ?? new NullLogger());

        $builder->addModelTransformer($dataTransformer, true);

        $provider = $this->mediaPool->getProvider($options['provider_name']);
        $provider->buildMediaType($builder);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'provider_name' => 'sonata.media.provider.image',
            'context' => 'api',
        ]);
    }

    public function getParent(): ?string
    {
        return ApiDoctrineMediaType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'sonata_media_api_form_media';
    }
}
