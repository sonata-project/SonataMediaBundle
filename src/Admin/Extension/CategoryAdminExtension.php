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

namespace Sonata\MediaBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Model\Media;

/**
 * @phpstan-extends AbstractAdminExtension<Media>
 */
final class CategoryAdminExtension extends AbstractAdminExtension
{
    private CategoryManagerInterface $categoryManager;

    private ContextManagerInterface $contextManager;

    public function __construct(
        CategoryManagerInterface $categoryManager,
        ContextManagerInterface $contextManager
    ) {
        $this->categoryManager = $categoryManager;
        $this->contextManager = $contextManager;
    }

    public function configurePersistentParameters(AdminInterface $admin, array $parameters): array
    {
        if (!$admin->hasRequest()) {
            return $parameters;
        }

        $context = $parameters['context'] ?? null;
        if (!\is_string($context)) {
            return $parameters;
        }

        $request = $admin->getRequest();
        $categoryId = $request->query->get('category');
        if (null === $categoryId) {
            $rootCategories = $this->categoryManager->getRootCategoriesForContext(
                $this->contextManager->find($context)
            );
            $rootCategory = current($rootCategories);

            if (false !== $rootCategory) {
                $categoryId = $rootCategory->getId();
            }
        }

        return array_merge($parameters, [
            'category' => $categoryId,
        ]);
    }

    public function alterNewInstance(AdminInterface $admin, object $object): void
    {
        $categoryId = $admin->getPersistentParameter('category');
        if (null === $categoryId) {
            return;
        }

        $category = $this->categoryManager->find($categoryId);
        if (null === $category) {
            return;
        }

        $context = $object->getContext();
        $categoryContext = $category->getContext();
        if (null === $categoryContext || $categoryContext->getId() !== $context) {
            return;
        }

        $object->setCategory($category);
    }

    public function configureFormFields(FormMapper $form): void
    {
        $admin = $form->getAdmin();
        $media = $admin->hasSubject() ? $admin->getSubject() : $admin->getNewInstance();

        $form->add('category', ModelListType::class, [], [
            'link_parameters' => [
                'context' => $media->getContext(),
                'hide_context' => true,
                'mode' => 'tree',
            ],
        ]);
    }

    public function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter->add('category', null, ['show_filter' => false]);
    }
}
