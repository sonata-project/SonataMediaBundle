<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\MediaBundle\Form\Type;

use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Class ApiMediaType
 *
 * @package Sonata\MediaBundle\Form\Type
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class ApiMediaType extends AbstractType
{
    /**
     * @var Pool $mediaPool
     */
    protected $mediaPool;

    /**
     * @var string $class
     */
    protected $class;

    /**
     * @param Pool   $mediaPool
     * @param string $class
     */
    public function __construct(Pool $mediaPool, $class)
    {
        $this->mediaPool = $mediaPool;
        $this->class     = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new ProviderDataTransformer($this->mediaPool, $this->class, array(
            'empty_on_new' => false
        )), true);

        $provider = $this->mediaPool->getProvider($options['provider_name']);
        $provider->buildMediaType($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'provider_name'   => "sonata.media.provider.image",
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return "sonata_media_api_form_doctrine_media";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "sonata_media_api_form_media";
    }


}