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

namespace Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\CollectionType;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @phpstan-extends AbstractAdmin<\Sonata\MediaBundle\Model\GalleryInterface<\Sonata\MediaBundle\Model\GalleryItemInterface>>
 */
final class GalleryAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Gallery';

    private Pool $pool;

    /**
     * @phpstan-param class-string<\Sonata\MediaBundle\Model\GalleryInterface<\Sonata\MediaBundle\Model\GalleryItemInterface>> $class
     */
    public function __construct(string $code, string $class, string $baseControllerName, Pool $pool)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
    }

    protected function prePersist(object $object): void
    {
        $parameters = $this->getPersistentParameters();

        $object->setContext($parameters['context']);
    }

    protected function postUpdate(object $object): void
    {
        $object->reorderGalleryItems();
    }

    protected function configurePersistentParameters(): array
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'context' => $this->getRequest()->query->get('context', $this->pool->getDefaultContext()),
        ];
    }

    protected function alterNewInstance(object $object): void
    {
        if (!$this->hasRequest()) {
            return;
        }

        $object->setContext($this->getRequest()->query->get('context'));
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('gallery', ['class' => 'col-md-9'])->end()
            ->with('options', ['class' => 'col-md-3'])->end();

        $context = $this->getPersistentParameter('context', $this->pool->getDefaultContext());

        $formats = [];
        foreach ($this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = [];
        foreach ($this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        $form
            ->with('options')
                ->add('context', ChoiceType::class, [
                    'choices' => $contexts,
                    'choice_translation_domain' => 'SonataMediaBundle',
                ])
                ->add('enabled', null, ['required' => false])
                ->add('name')
                ->ifTrue([] !== $formats)
                    ->add('defaultFormat', ChoiceType::class, ['choices' => $formats])
                ->ifEnd()
            ->end()
            ->with('gallery')
                ->add('galleryItems', CollectionType::class, ['by_reference' => false], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                    'link_parameters' => ['context' => $context],
                    'admin_code' => 'sonata.media.admin.gallery_item',
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('enabled', 'boolean', ['editable' => true])
            ->add('context', 'trans', ['value_translation_domain' => 'SonataMediaBundle'])
            ->add('defaultFormat', 'trans', ['value_translation_domain' => 'SonataMediaBundle']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('enabled')
            ->add('context', null, [
                'show_filter' => false,
            ]);
    }
}
