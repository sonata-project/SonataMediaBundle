<?php

namespace Sonata\MediaBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MultiUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('context', 'hidden', array('data' => $options['context']))
            ->add('provider', 'hidden', array('data' => $options['provider']))
            ->add('files', 'file', array(
                'multiple' => true
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'context' => 'default',
            'provider' => null
        ));
    }

    public function getName()
    {
        return 'multi_upload';
    }
}