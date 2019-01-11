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

namespace Sonata\MediaBundle\Admin\PHPCR;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MediaAdmin extends Admin
{
    /**
     * Path to the root node of media documents.
     *
     * @var string
     */
    protected $root;

    /**
     * @param string $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = $this->getModelManager()->createQuery($this->getClass(), 'a', $this->root);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $options = [
            'choices' => [],
            'required' => false,
            'multiple' => false,
            'expanded' => false,
        ];

        foreach ($this->pool->getContexts() as $name => $context) {
            $options['choices'][$this->trans('media_context.'.$name)] = $name;
        }

        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context', null, [
                'show_filter' => true !== $this->getPersistentParameter('hide_context'),
            ], ChoiceType::class, $options);
        $datagridMapper
            ->add('width')
            ->add('height')
            ->add('contentType')
        ;

        $providers = [];

        $providerNames = (array) $this->pool->getProviderNamesByContext($this->getPersistentParameter('context', $this->pool->getDefaultContext()));
        foreach ($providerNames as $name) {
            $providers[$this->trans($name, [])] = $name;
        }

        $datagridMapper->add('providerName', null, [
            'field_options' => [
                'choices' => $providers,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ],
            'field_type' => ChoiceType::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        // Allow path in id parameter
        $collection->add('view', $this->getRouterIdParameter().'/view', [], ['id' => '.+', '_method' => 'GET']);
        $collection->add('show', $this->getRouterIdParameter().'/show', [
                '_controller' => sprintf('%s:%s', $this->baseControllerName, 'view'),
            ],
            ['id' => '.+', '_method' => 'GET']
        );
    }
}
