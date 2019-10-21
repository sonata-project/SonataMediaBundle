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
 * @final since sonata-project/media-bundle 3.21.0
 */
class GalleryAdmin extends AbstractAdmin
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

    /**
     * {@inheritdoc}
     */
    public function prePersist($gallery)
    {
        $parameters = $this->getPersistentParameters();

        $gallery->setContext($parameters['context']);
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($gallery)
    {
        $gallery->reorderGalleryHasMedia();
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        return array_merge($parameters, [
            'context' => $this->getRequest()->get('context', $this->pool->getDefaultContext()),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $gallery = parent::getNewInstance();

        if ($this->hasRequest()) {
            $gallery->setContext($this->getRequest()->get('context'));
        }

        return $gallery;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        // define group zoning
        $formMapper
            ->with('Gallery', ['class' => 'col-md-9'])->end()
            ->with('Options', ['class' => 'col-md-3'])->end()
        ;

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

        $formMapper
            ->with('Options')
                ->add('context', ChoiceType::class, ['choices' => $contexts])
                ->add('enabled', null, ['required' => false])
                ->add('name')
                ->ifTrue($formats)
                    ->add('defaultFormat', ChoiceType::class, ['choices' => $formats])
                ->ifEnd()
            ->end()
            ->with('Gallery')
                ->add('galleryHasMedias', CollectionType::class, ['by_reference' => false], [
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                    'link_parameters' => ['context' => $context],
                    'admin_code' => 'sonata.media.admin.gallery_has_media',
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('enabled', 'boolean', ['editable' => true])
            ->add('context', 'trans', ['catalogue' => 'SonataMediaBundle'])
            ->add('defaultFormat', 'trans', ['catalogue' => 'SonataMediaBundle'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('context', null, [
                'show_filter' => false,
            ])
        ;
    }
}
