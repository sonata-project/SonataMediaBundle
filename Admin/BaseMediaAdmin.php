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
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;

use Knp\Menu\ItemInterface as MenuItemInterface;

abstract class BaseMediaAdmin extends Admin
{
    protected $pool;

    protected $categoryManager;

    /**
     * @param string                   $code
     * @param string                   $class
     * @param string                   $baseControllerName
     * @param Pool                     $pool
     * @param CategoryManagerInterface $categoryManager
     */
    public function __construct($code, $class, $baseControllerName, Pool $pool, CategoryManagerInterface $categoryManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;

        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('description')
            ->add('enabled')
            ->add('size')
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

        $formMapper->add('providerName', 'hidden');

        $formMapper->getFormBuilder()->addModelTransformer(new ProviderDataTransformer($this->pool, $this->getClass()), true);

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }

        $formMapper->add('category', 'sonata_type_model_list', array(), array(
            'link_parameters' => array(
                'context'      => $media->getContext(),
                'hide_context' => true,
                'mode'         => 'tree',
            )
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
        $parameters = parent::getPersistentParameters();

        if (!$this->hasRequest()) {
            return $parameters;
        }

        if ($filter = $this->getRequest()->get('filter')  && array_key_exists('context', $this->getRequest()->get('filter'))) {
            $context = $filter['context']['value'];
        } else {
            $context   = $this->getRequest()->get('context', $this->pool->getDefaultContext());
        }

        $providers = $this->pool->getProvidersByContext($context);
        $provider  = $this->getRequest()->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (count($providers) == 1 && null === $provider) {
            $provider = array_shift($providers)->getName();
            $this->getRequest()->query->set('provider', $provider);
        }

        $categoryId = $this->getRequest()->get('category');

        if (!$categoryId) {
            $categoryId = $this->categoryManager->getRootCategory($context)->getId();
        }

        return array_merge($parameters,array(
            'context'      => $context,
            'category'     => $categoryId,
            'hide_context' => (bool)$this->getRequest()->get('hide_context')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $media = parent::getNewInstance();

        if ($this->hasRequest()) {
            if ($this->getRequest()->isMethod('POST')) {
                $media->setProviderName($this->getRequest()->get(sprintf('%s[providerName]', $this->getUniqid()), null, true));
            } else {
                $media->setProviderName($this->getRequest()->get('provider'));
            }

            $media->setContext($context = $this->getRequest()->get('context'));

            if ($categoryId = $this->getPersistentParameter('category')) {
                $category = $this->categoryManager->find($categoryId);

                if ($category && $category->getContext()->getId() == $context) {
                    $media->setCategory($category);
                }
            }
        }

        return $media;
    }

    /**
     * @return null|\Sonata\MediaBundle\Provider\Pool
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectMetadata($object)
    {
        $provider = $this->pool->getProvider($object->getProviderName());

        $url = $provider->generatePublicUrl($object, $provider->getFormatName($object, 'admin'));

        return new Metadata($object->getName(), $object->getDescription(), $url);
    }
}
