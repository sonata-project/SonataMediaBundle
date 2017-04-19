<?php

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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaType extends AbstractType implements LoggerAwareInterface
{
    /**
     * @var Pool
     */
    protected $mediaPool;

    /**
     * @var string
     */
    protected $class;

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Pool   $mediaPool
     * @param string $class
     */
    public function __construct(Pool $mediaPool, $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class = $class;
        $this->logger = new NullLogger();
    }

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataTransformer = new ProviderDataTransformer($this->mediaPool, $this->class, array(
            'empty_on_new' => false,
        ));
        $dataTransformer->setLogger($this->logger);

        $builder->addModelTransformer($dataTransformer, true);

        $provider = $this->mediaPool->getProvider($options['provider_name']);
        $provider->buildMediaType($builder);
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method.
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
        $resolver->setDefaults(array(
            'provider_name' => 'sonata.media.provider.image',
            'context' => 'api',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // NEXT_MAJOR: Return 'Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType'
        // (when requirement of Symfony is >= 2.8)
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\MediaBundle\Form\Type\ApiDoctrineMediaType'
            : 'sonata_media_api_form_doctrine_media';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_media_api_form_media';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
