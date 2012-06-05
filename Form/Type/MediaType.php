<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Symfony\Component\Form\FormBuilderInterface;

class MediaType extends AbstractType
{
    protected $pool;

    protected $class;


    /**
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     * @param string                            $class
     */
    public function __construct(Pool $pool, $class)
    {
        $this->pool  = $pool;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->appendNormTransformer(new ProviderDataTransformer($this->pool, array(
            'provider' => $options['provider'],
            'context'  => $options['context'],
        )));

        $this->pool->getProvider($options['provider'])->buildMediaType($builder);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'data_class' => $this->class,
            'provider'   => null,
            'context'    => null
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonata_media_type';
    }
}
