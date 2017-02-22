<?php

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
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

abstract class BaseMediaAdmin extends AbstractAdmin
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var CategoryManagerInterface
     */
    protected $categoryManager;

    /**
     * @param string                   $code
     * @param string                   $class
     * @param string                   $baseControllerName
     * @param Pool                     $pool
     * @param CategoryManagerInterface $categoryManager
     */
    public function __construct($code, $class, $baseControllerName, Pool $pool, CategoryManagerInterface $categoryManager = null)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;

        $this->categoryManager = $categoryManager;
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

        $filter = $this->getRequest()->get('filter');
        if ($filter && array_key_exists('context', $this->getRequest()->get('filter'))) {
            $context = $filter['context']['value'];
        } else {
            $context = $this->getRequest()->get('context', $this->pool->getDefaultContext());
        }

        $providers = $this->pool->getProvidersByContext($context);
        $provider = $this->getRequest()->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (count($providers) == 1 && null === $provider) {
            $provider = array_shift($providers)->getName();
            $this->getRequest()->query->set('provider', $provider);
        }

        $categoryId = $this->getRequest()->get('category');

        if (null !== $this->categoryManager && !$categoryId) {
            $categoryId = $this->categoryManager->getRootCategory($context)->getId();
        }

        return array_merge($parameters, array(
            'context' => $context,
            'category' => $categoryId,
            'hide_context' => (bool) $this->getRequest()->get('hide_context'),
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
                $uniqid = $this->getUniqid();

                if (method_exists('Symfony\Component\HttpFoundation\JsonResponse', 'transformJsonError')) {
                    // NEXT_MAJOR remove this block when dropping sf < 2.8 compatibility
                    $media->setProviderName(
                        $this->getRequest()->get(sprintf('%s[providerName]', $uniqid), null, true)
                    );
                } else {
                    $providerParams = $this->getRequest()->get($uniqid);
                    $media->setProviderName($providerParams['providerName']);
                }
            } else {
                $media->setProviderName($this->getRequest()->get('provider'));
            }

            $media->setContext($context = $this->getRequest()->get('context'));

            if (null !== $this->categoryManager && $categoryId = $this->getPersistentParameter('category')) {
                $category = $this->categoryManager->find($categoryId);

                if ($category && $category->getContext()->getId() == $context) {
                    $media->setCategory($category);
                }
            }
        }

        return $media;
    }

    /**
     * @return Pool
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

        // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
        $hiddenType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\HiddenType'
            : 'hidden';

        $formMapper->add('providerName', $hiddenType);

        $formMapper->getFormBuilder()->addModelTransformer(new ProviderDataTransformer($this->pool, $this->getClass()), true);

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }

        if (null !== $this->categoryManager) {
            // NEXT_MAJOR: Keep FQCN when bumping Symfony requirement to 2.8+.
            $modelListType = method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Sonata\AdminBundle\Form\Type\ModelListType'
                : 'sonata_type_model_list';

            $formMapper->add('category', $modelListType, array(), array(
                'link_parameters' => array(
                    'context' => $media->getContext(),
                    'hide_context' => true,
                    'mode' => 'tree',
                ),
            ));
        }
    }
}
