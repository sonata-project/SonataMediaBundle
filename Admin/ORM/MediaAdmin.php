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

        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context', null, array(
                'show_filter' => $this->getPersistentParameter('hide_context') !== true,
            ), 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', $options);

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

        $datagridMapper->add('providerName', 'Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter', array(
            'field_options' => array(
                'choices' => $providers,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ),
            'field_type' => 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        ));
    }
}
