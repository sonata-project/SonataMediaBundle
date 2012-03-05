<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\AdminBundle\Validator\ErrorElement;

class GalleryAdmin extends Admin
{
    protected $pool;

    /**
     * @param $code
     * @param $class
     * @param $baseControllerName
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     */
    public function __construct($code, $class, $baseControllerName, Pool $pool)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $context = $this->getPersistentParameter('context');

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $formats = array();
        foreach((array)$this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = array();
        foreach((array)$this->pool->getContexts() as $context => $format) {
            $contexts[$context] = $context;
        }

        $formMapper
            ->add('context', 'choice', array('choices' => $contexts))
            ->add('enabled', null, array('required' => false))
            ->add('name')
            ->add('defaultFormat', 'choice', array('choices' => $formats))
            ->add('galleryHasMedias', 'sonata_type_collection', array(
                'by_reference' => false
            ), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position',
                'link_parameters' => array('context' => $context)
            ))
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('enabled')
            ->addIdentifier('name')
            ->add('context')
            ->add('defaultFormat')
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\MediaBundle\Model\GalleryInterface $gallery
     */
    public function validate(ErrorElement $errorElement, $gallery)
    {
        $formats = $this->pool->getFormatNamesByContext($gallery->getContext());

        if (!array_key_exists($gallery->getDefaultFormat(), $formats)) {
            $errorElement->with('defaultFormat')
                ->addViolation('invalid format')
            ->end();
        }
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
            ->add('context')
        ;
    }

    /**
     * @param $media
     * @return void
     */
    public function prePersist($gallery)
    {
        $parameters = $this->getPersistentParameters();

        $gallery->setContext($parameters['context']);
    }

    /**
     * @return array
     */
    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return array();
        }

        return array(
            'context'  => $this->getRequest()->get('context', 'default'),
        );
    }

    /**
     * @return mixed
     */
    public function getNewInstance()
    {
        $gallery = parent::getNewInstance();

        if ($this->hasRequest()) {
            $gallery->setContext($this->getRequest()->get('context'));
        }

        return $gallery;
    }

}