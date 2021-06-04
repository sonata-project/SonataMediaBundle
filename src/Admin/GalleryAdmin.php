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

final class GalleryAdmin extends AbstractAdmin
{
    /**
     * @var Pool
     */
    protected $pool;

    protected $classnameLabel = 'Gallery';

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName, Pool $pool)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
    }

    public function prePersist($gallery): void
    {
        $parameters = $this->getPersistentParameters();

        $gallery->setContext($parameters['context']);
    }

    public function postUpdate(object $object): void
    {
        $object->reorderGalleryItems();
    }

    public function configurePersistentParameters(): array
    {
        if (!$this->hasRequest()) {
            return [];
        }

        return [
            'context' => $this->getRequest()->get('context', $this->pool->getDefaultContext()),
        ];
    }

    public function alterNewInstance(object $object): void
    {
        if ($this->hasRequest()) {
            $object->setContext($this->getRequest()->get('context'));
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        // define group zoning
        $form
            // NEXT_MAJOR: Change Gallery key to `form_group.gallery` and update translations files.
            ->with('Gallery', ['class' => 'col-md-9'])->end()
            // NEXT_MAJOR: Change Options key to `form_group.options` and update translations files.
            ->with('Options', ['class' => 'col-md-3'])->end();

        $context = $this->getPersistentParameter('context');

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $formats = [];
        foreach ((array) $this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = [];
        foreach ((array) $this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        $form
            ->with('Options')
                ->add('context', ChoiceType::class, [
                    'choices' => $contexts,
                    'choice_translation_domain' => 'SonataMediaBundle',
                ])
                ->add('enabled', null, ['required' => false])
                ->add('name')
                ->ifTrue(!empty($formats))
                    ->add('defaultFormat', ChoiceType::class, ['choices' => $formats])
                ->ifEnd()
            ->end()
            ->with('Gallery')
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
            ->add('context', 'trans', ['catalogue' => 'SonataMediaBundle'])
            ->add('defaultFormat', 'trans', ['catalogue' => 'SonataMediaBundle']);
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
