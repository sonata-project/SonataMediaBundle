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
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;

use Knp\Menu\ItemInterface as MenuItemInterface;

abstract class BaseMediaAdmin extends Admin
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
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
//            ->add('image', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))
            ->add('custom', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig'))
            ->add('enabled', 'boolean')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();

        if (!$media) {
            $media = $this->getNewInstance();
        }

        if(!$media || !$media->getProviderName()) {
            return;
        }

        $formMapper->getFormBuilder()->appendNormTransformer(new ProviderDataTransformer($this->pool));

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
    }

    /**
     * @param \Sonata\AdminBundle\Show\ShowMapper $filter
     * @return void
     */
    protected function configureShowField(ShowMapper $filter)
    {
        // TODO: Implement configureShowField() method.
    }

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('view', $this->getRouterIdParameter().'/view');
    }

    /**
     * @param $media
     * @return void
     */
    public function prePersist($media)
    {
        $parameters = $this->getPersistentParameters();
        $media->setContext($parameters['context']);
    }

    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return array();
        }

        return array(
            'provider' => $this->getRequest()->get('provider'),
            'context'  => $this->getRequest()->get('context', $this->pool->getDefaultContext()),
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

    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param $action
     * @param null|\Sonata\AdminBundle\Admin\Admin $childAdmin
     * @return
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
    {
        if (!in_array($action, array('edit', 'view'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $this->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_edit_media'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        $menu->addChild(
            $this->trans('sidemenu.link_media_view'),
            array('uri' => $admin->generateUrl('view', array('id' => $id)))
        );
    }

    /**
     * @return null|\Sonata\MediaBundle\Provider\Pool
     */
    public function getPool()
    {
        return $this->pool;
    }
}