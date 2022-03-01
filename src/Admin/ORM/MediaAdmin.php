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
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

final class MediaAdmin extends BaseMediaAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $options = [
            'choices' => [],
        ];

        foreach ($this->pool->getContexts() as $name => $context) {
            $options['choices'][$name] = $name;
        }

        $filter
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context', null, [
                'field_type' => ChoiceType::class,
                'field_options' => $options,
                'show_filter' => true !== $this->getPersistentParameter('hide_context'),
            ]);

        if (null !== $this->categoryManager) {
            $filter->add('category', null, ['show_filter' => false]);
        }

        $filter
            ->add('width')
            ->add('height')
            ->add('contentType');

        $providersChoices = [];

        $providers = $this->pool->getProvidersByContext($this->getPersistentParameter('context', $this->pool->getDefaultContext()));
        foreach ($providers as $provider) {
            $name = $provider->getName();
            $translatedName = $this->getTranslator()->trans(
                $name,
                [],
                $provider->getProviderMetadata()->getDomain() ?? $this->getTranslationDomain()
            );

            $providersChoices[$translatedName] = $name;
        }

        $filter->add('providerName', ChoiceFilter::class, [
            'field_options' => [
                'choices' => $providersChoices,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ],
            'field_type' => ChoiceType::class,
        ]);
    }
}
