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
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;

use Knp\Menu\ItemInterface as MenuItemInterface;

abstract class BaseMediaAdmin extends Admin
{
    protected $pool;

    /**
     * @param string                            $code
     * @param string                            $class
     * @param string                            $baseControllerName
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     */
    public function __construct($code, $class, $baseControllerName, Pool $pool)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
//            ->add('image', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig'))
            ->add('custom', 'string', array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig'))
            ->add('enabled', 'boolean', array('editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'view' => array(),
                    'edit' => array(),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();

        if (!$media) {
            $media = $this->getNewInstance();
        }

        if (!$media || !$media->getProviderName()) {
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
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('view', $this->getRouterIdParameter() . '/view');
        $collection->add('show', $this->getRouterIdParameter() . '/show', array(
            '_controller' => sprintf('%s:%s', $this->baseControllerName, 'view')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($media)
    {
        $parameters = $this->getPersistentParameters();
        $media->setContext($parameters['context']);
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return array();
        }

        $context   = $this->getRequest()->get('context', $this->pool->getDefaultContext());
        $providers = $this->pool->getProvidersByContext($context);
        $provider  = $this->getRequest()->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (count($providers) == 1 && null === $provider) {
            $provider = array_shift($providers)->getName();
            $this->getRequest()->query->set('provider', $provider);
        }

        return array(
            'provider' => $provider,
            'context'  => $context,
        );
    }

    /**
     * {@inheritdoc}
     */
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
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
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