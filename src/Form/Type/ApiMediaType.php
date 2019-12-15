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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaType extends AbstractType implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Pool
     */
    protected $mediaPool;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class
     */
    public function __construct(Pool $mediaPool, $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class = $class;
        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataTransformer = new ProviderDataTransformer($this->mediaPool, $this->class, [
            'empty_on_new' => false,
        ]);
        $dataTransformer->setLogger($this->logger);

        $builder->addModelTransformer($dataTransformer, true);

        $provider = $this->mediaPool->getProvider($options['provider_name']);
        $provider->buildMediaType($builder);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated Remove it when bumping requirements to Symfony >=2.7
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'provider_name' => 'sonata.media.provider.image',
            'context' => 'api',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ApiDoctrineMediaType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_media_api_form_media';
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.22, to be removed in version 4.0.
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
