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
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Knp\Bundle\MenuBundle\MenuItem;

class MediaAdmin extends Admin
{
    protected $pool = null;

    public function __construct($code, $class, $baseControllerName, $pool)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context')
        ;

       $providers = array();

        foreach($this->pool->getProviderNamesByContext('default') as $name) {
            $providers[$name] = $name;
        }

        $datagridMapper->add('providerName', 'choice', array(
            'filter_field_options'=> array(
                'choices' => $providers,
                'required' => false,
                'multiple' => true,
                'expanded' => true
            )
        ));
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('image', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))
            ->add('custom', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig'))
            ->add('enabled')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();

        if (!$media) {
            $media = $this->getNewInstance();
        }

        if(!$media || !$media->getProviderName()) {
            return;
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId() > 0) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
    }

    /**
     *
     * @param DatagridMapper
     */
    protected function configureShowField(ShowMapper $filter)
    {
        // TODO: Implement configureShowField() method.
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    public function prePersist($media)
    {
        $parameters = $this->getPersistentParameters();
        $media->setContext($parameters['context']);

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
        $this->pool->postUpdate($media);
    }

    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return array();
        }

        return array(
            'provider' => $this->getRequest()->get('provider'),
            'context'  => $this->getRequest()->get('context', 'default'),
        );
    }

    public function getNewInstance()
    {
        $media = parent::getNewInstance();

        if ($this->hasRequest()) {
            $media->setProviderName($this->getRequest()->get('provider'));
            $media->setContext($this->getRequest()->get('context'));
        }

        return $media;
    }

    protected function configureSideMenu(MenuItem $menu, $action, Admin $childAdmin = null)
    {
        if (!in_array($action, array('edit', 'view'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $this->getRequest()->get('id');

        $menu->addChild(
            $this->trans('slidemenu.link_edit_media', array(), 'SonataAdminBundle'),
            $admin->generateUrl('edit', array('id' => $id))
        );

        $menu->addChild(
            $this->trans('slidemenu.link_media_view', array(), 'SonataAdminBundle'),
            $admin->generateUrl('view', array('id' => $id))
        );
    }

    public function getPool()
    {
        return $this->pool;
    }
}