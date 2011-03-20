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

class MediaAdmin extends Admin
{
    protected $pool = null;
    
    protected $list = array(
        'image'  => array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig', 'type' => 'string'),
        'custom' => array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig', 'type' => 'string'),
        'enabled',
    );

    protected $filter = array(
        'name',
        'providerReference',
        'enabled',
    );

    public function __construct($class, $baseControllerName, $pool)
    {
        parent::__construct($class, $baseControllerName);

        $this->pool = $pool;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $media = $formMapper->getForm()->getData();

        $provider = $this->pool->getProvider($media->getProviderName());

        if($media->getId() > 0) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
    }

    public function configureDatagridFilters(DatagridMapper $datagrid)
    {

    }

    public function configureListFields(ListMapper $list)
    {

    }

    public function prePersist($media)
    {
        $this->pool->prePersist($media);
    }

    public function postPersist($media)
    {
        $this->pool->postPersist($media);
    }

    public function preUpdate($media)
    {
      $this->pool->preUpdate($media);
    }

    public function postUpdate($media) 
    {
      $this->pool->preUpdate($media);
    }

    public function getPersistentParameters()
    {
        if(!$this->getRequest()) {
            return array();
        }

        return array(
            'provider' => $this->getRequest()->get('provider'),
            'context'  => $this->getRequest()->get('context'),
        );
    }

    public function getNewInstance()
    {
        $media = parent::getNewInstance();
        
        if($this->getRequest()) {
            $media->setProviderName($this->getRequest()->get('provider'));
            $media->setContext($this->getRequest()->get('context'));
        }

        return $media;
    }

}