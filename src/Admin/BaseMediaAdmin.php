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
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * @phpstan-extends AbstractAdmin<MediaInterface>
 */
abstract class BaseMediaAdmin extends AbstractAdmin
{
    protected Pool $pool;

    protected ?CategoryManagerInterface $categoryManager = null;

    protected ?ContextManagerInterface $contextManager = null;

    protected $classnameLabel = 'Media';

    /**
     * NEXT_MAJOR: Change to (Pool, ?CategoryManagerInterface, ?ContextManagerInterface).
     *
     * @param Pool|string                          $pool
     * @param CategoryManagerInterface|string|null $categoryManager
     * @param ContextManagerInterface|string|null  $contextManager
     *
     * @phpstan-param CategoryManagerInterface|class-string<MediaInterface>|null $categoryManager
     */
    public function __construct(
        $pool,
        $categoryManager = null,
        $contextManager = null,
        ?Pool $deprecatedPool = null,
        ?CategoryManagerInterface $deprecatedCategoryManager = null,
        ?ContextManagerInterface $deprecatedContextManager = null
    ) {
        // NEXT_MAJOR: Keep the if part.
        if ($pool instanceof Pool) {
            \assert(!\is_string($categoryManager));
            \assert(!\is_string($contextManager));

            parent::__construct();

            $this->pool = $pool;
            $this->categoryManager = $categoryManager;
            $this->contextManager = $contextManager;
        } else {
            \assert(\is_string($categoryManager));
            \assert(\is_string($contextManager));
            \assert(null !== $deprecatedPool);

            parent::__construct($pool, $categoryManager, $contextManager);

            $this->pool = $deprecatedPool;
            $this->categoryManager = $deprecatedCategoryManager;
            $this->contextManager = $deprecatedContextManager;
        }
    }

    final public function getObjectMetadata(object $object): MetadataInterface
    {
        $provider = $this->pool->getProvider($object->getProviderName());

        $url = $provider->generatePublicUrl(
            $object,
            $provider->getFormatName($object, MediaProviderInterface::FORMAT_ADMIN)
        );

        return new Metadata($this->toString($object), $object->getDescription(), $url);
    }

    protected function prePersist(object $object): void
    {
        $parameters = $this->getPersistentParameters();
        $object->setContext($parameters['context']);
    }

    protected function configurePersistentParameters(): array
    {
        $parameters = [];

        if (!$this->hasRequest()) {
            return $parameters;
        }

        $request = $this->getRequest();

        // TODO: Change to $request->query->all('filter') when support for Symfony < 5.1 is dropped.
        $filter = $request->query->all()['filter'] ?? [];

        if (\is_array($filter) && \array_key_exists('context', $filter)) {
            $context = $filter['context']['value'];
        } else {
            $context = $request->query->get('context', $this->pool->getDefaultContext());
        }

        $providers = $this->pool->getProvidersByContext($context);
        $provider = $request->query->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (1 === \count($providers) && null === $provider) {
            $provider = array_shift($providers)->getName();
            $request->query->set('provider', $provider);
        }

        // if there is a post server error, provider is not posted and in case of
        // multiple providers, it has to be persistent to not being lost
        if (1 < \count($providers) && null !== $provider) {
            $parameters['provider'] = $provider;
        }

        $categoryId = $request->query->get('category');

        if (null !== $this->categoryManager && null !== $this->contextManager && null === $categoryId) {
            $rootCategories = $this->categoryManager->getRootCategoriesForContext($this->contextManager->find($context));
            $rootCategory = current($rootCategories);

            if (false !== $rootCategory) {
                $categoryId = $rootCategory->getId();
            }
        }

        return array_merge($parameters, [
            'context' => $context,
            'category' => $categoryId,
            'hide_context' => $request->query->getBoolean('hide_context'),
        ]);
    }

    protected function alterNewInstance(object $object): void
    {
        if (!$this->hasRequest()) {
            return;
        }

        $request = $this->getRequest();

        if ($request->isMethod('POST')) {
            $uniqId = $this->getUniqId();

            // TODO: Change to $request->request->all($uniqid)['providerName'] when support for Symfony < 5.1 is dropped.
            $data = $request->request->all()[$uniqId] ?? [];
            \assert(\is_array($data));

            $providerName = $data['providerName'];
        } else {
            $providerName = $request->query->get('provider');
        }

        $context = $request->query->get('context');

        $object->setProviderName($providerName);
        $object->setContext($context);

        $categoryId = $this->getPersistentParameter('category');

        if (null === $this->categoryManager || null === $categoryId) {
            return;
        }

        $category = $this->categoryManager->find($categoryId);

        if (null === $category) {
            return;
        }

        $categoryContext = $category->getContext();

        if (null === $categoryContext || $categoryContext->getId() !== $context) {
            return;
        }

        $object->setCategory($category);
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

        if (null === $media->getProviderName()) {
            return;
        }

        $form->add('providerName', HiddenType::class);

        $form->getFormBuilder()->addModelTransformer(new ProviderDataTransformer($this->pool, $this->getClass()), true);

        $provider = $this->pool->getProvider($media->getProviderName());

        if (null !== $media->getId()) {
            $provider->buildEditForm($form);
        } else {
            $provider->buildCreateForm($form);
        }

        if (null === $this->categoryManager) {
            return;
        }

        $form->add('category', ModelListType::class, [], [
            'link_parameters' => [
                'context' => $media->getContext(),
                'hide_context' => true,
                'mode' => 'tree',
            ],
        ]);
    }
}
