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
 * @author Hugo Briand <briand@ekino.com>
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
     * @param string $class
     *
     * @phpstan-param class-string<\Sonata\MediaBundle\Model\MediaInterface> $class
     */
    public function __construct(Pool $mediaPool, $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class = $class;
        $this->logger = new NullLogger();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        \assert(null !== $this->logger);

        $dataTransformer = new ProviderDataTransformer($this->mediaPool, $this->class, [
            'empty_on_new' => false,
        ]);
        $dataTransformer->setLogger($this->logger);

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

    public function getParent()
    {
        return ApiDoctrineMediaType::class;
    }

    public function getBlockPrefix()
    {
        return 'sonata_media_api_form_media';
    }
}
