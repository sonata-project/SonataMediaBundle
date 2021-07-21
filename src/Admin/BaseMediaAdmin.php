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
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @phpstan-extends AbstractAdmin<\Sonata\MediaBundle\Model\MediaInterface>
 */
abstract class BaseMediaAdmin extends AbstractAdmin
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var CategoryManagerInterface|null
     */
    protected $categoryManager;

    /**
     * @var ContextManagerInterface|null
     */
    protected $contextManager;

    protected $classnameLabel = 'Media';

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        Pool $pool,
        ?CategoryManagerInterface $categoryManager = null,
        ?ContextManagerInterface $contextManager = null
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->pool = $pool;
        $this->categoryManager = $categoryManager;
        $this->contextManager = $contextManager;
    }

    public function prePersist(object $object): void
    {
        $parameters = $this->getPersistentParameters();
        $object->setContext($parameters['context']);
    }

    public function configurePersistentParameters(): array
    {
        $parameters = [];

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $filter = $this->getRequest()->get('filter');
        if ($filter && \array_key_exists('context', $this->getRequest()->get('filter'))) {
            $context = $filter['context']['value'];
        } else {
            $context = $this->getRequest()->get('context', $this->pool->getDefaultContext());
        }

        $providers = $this->pool->getProvidersByContext($context);
        $provider = $this->getRequest()->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (1 === \count($providers) && null === $provider) {
            $provider = array_shift($providers)->getName();
            $this->getRequest()->query->set('provider', $provider);
        }

        // if there is a post server error, provider is not posted and in case of
        // multiple providers, it has to be persistent to not being lost
        if (1 < \count($providers) && null !== $provider) {
            $parameters['provider'] = $provider;
        }

        $categoryId = $this->getRequest()->get('category');

        if (null !== $this->categoryManager && null !== $this->contextManager && !$categoryId) {
            $rootCategories = $this->categoryManager->getRootCategoriesForContext($this->contextManager->find($context));
            $rootCategory = current($rootCategories);

            if (false !== $rootCategory) {
                $categoryId = $rootCategory->getId();
            }
        }

        return array_merge($parameters, [
            'context' => $context,
            'category' => $categoryId,
            'hide_context' => (bool) $this->getRequest()->get('hide_context'),
        ]);
    }

    public function alterNewInstance(object $object): void
    {
        if ($this->hasRequest()) {
            if ($this->getRequest()->isMethod('POST')) {
                $uniqid = $this->getUniqid();
                $object->setProviderName($this->getRequest()->get($uniqid)['providerName']);
            } else {
                $object->setProviderName($this->getRequest()->get('provider'));
            }

            $object->setContext($context = $this->getRequest()->get('context'));

            if (null !== $this->categoryManager && $categoryId = $this->getPersistentParameter('category')) {
                $category = $this->categoryManager->find($categoryId);

                if (!$category) {
                    return;
                }

                $categoryContext = $category->getContext();

                if ($categoryContext && $categoryContext->getId() === $context) {
                    $object->setCategory($category);
                }
            }
        }
    }

    public function getObjectMetadata(object $object): MetadataInterface
    {
        $provider = $this->pool->getProvider($object->getProviderName());

        $url = $provider->generatePublicUrl(
            $object,
            $provider->getFormatName($object, MediaProviderInterface::FORMAT_ADMIN)
        );

        return new Metadata($this->toString($object), $object->getDescription(), $url);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('description')
            ->add('enabled')
            ->add('size');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $media = $this->hasSubject() ? $this->getSubject() : $this->getNewInstance();

        if (!$media->getProviderName()) {
            return;
        }

        $form->add('providerName', HiddenType::class);

        $form->getFormBuilder()->addModelTransformer(new ProviderDataTransformer($this->pool, $this->getClass()), true);

        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($form);
        } else {
            $provider->buildCreateForm($form);
        }

        if (null !== $this->categoryManager) {
            $form->add('category', ModelListType::class, [], [
                'link_parameters' => [
                    'context' => $media->getContext(),
                    'hide_context' => true,
                    'mode' => 'tree',
                ],
            ]);
        }
    }
}
