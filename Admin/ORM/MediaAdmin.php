<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin\ORM;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;

class MediaAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $options = array(
            'choices' => array(),
        );

        foreach ($this->pool->getContexts() as $name => $context) {
            $options['choices'][$name] = $name;
        }

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $choiceType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
            : 'choice';

        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context', null, array(
                'show_filter' => $this->getPersistentParameter('hide_context') !== true,
            ), $choiceType, $options);

        if (null !== $this->categoryManager) {
            $datagridMapper->add('category', null, array('show_filter' => false));
        }

        $datagridMapper
            ->add('width')
            ->add('height')
            ->add('contentType')
        ;

        $providers = array();

        $providerNames = (array) $this->pool->getProviderNamesByContext($this->getPersistentParameter('context', $this->pool->getDefaultContext()));
        foreach ($providerNames as $name) {
            $providers[$name] = $name;
        }

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $ormChoiceType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter'
            : 'doctrine_orm_choice';

        $datagridMapper->add('providerName', $ormChoiceType, array(
            'field_options' => array(
                'choices' => $providers,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ),
            'field_type' => $choiceType,
        ));
    }
}
