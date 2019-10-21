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

namespace Sonata\MediaBundle\Admin\ORM;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $options = [
            'choices' => [],
        ];

        foreach ($this->pool->getContexts() as $name => $context) {
            $options['choices'][$name] = $name;
        }

        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context', null, [
                'show_filter' => true !== $this->getPersistentParameter('hide_context'),
            ], ChoiceType::class, $options);

        if (null !== $this->categoryManager) {
            $datagridMapper->add('category', null, ['show_filter' => false]);
        }

        $datagridMapper
            ->add('width')
            ->add('height')
            ->add('contentType')
        ;

        $providers = [];

        $providerNames = (array) $this->pool->getProviderNamesByContext($this->getPersistentParameter('context', $this->pool->getDefaultContext()));
        foreach ($providerNames as $name) {
            $providers[$name] = $name;
        }

        $datagridMapper->add('providerName', ChoiceFilter::class, [
            'field_options' => [
                'choices' => $providers,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ],
            'field_type' => ChoiceType::class,
        ]);
    }
}
