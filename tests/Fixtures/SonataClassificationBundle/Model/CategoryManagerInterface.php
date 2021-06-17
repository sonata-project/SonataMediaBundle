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

namespace Sonata\ClassificationBundle\Model;

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\Doctrine\Model\PageableManagerInterface;

/**
 * @method PagerInterface         getRootCategoriesPager(int $page = 1, int $limit = 25, array $criteria = [])
 * @method PagerInterface         getSubCategoriesPager(int $categoryId, int $page = 1, int $limit = 25, array $criteria = [])
 * @method CategoryInterface[]    getRootCategoriesForContext(ContextInterface|null $context)
 * @method CategoryInterface[]    getAllRootCategories(bool $loadChildren = true)
 * @method CategoryInterface[]    getRootCategoriesSplitByContexts(bool $loadChildren = true)
 * @method CategoryInterface[]    getCategories(ContextInterface|string|null $context)
 * @method CategoryInterface|null getBySlug(string $slug, ContextInterface|string|null $context, bool $enabled = true)
 */
interface CategoryManagerInterface extends ManagerInterface, PageableManagerInterface
{
    /**
     * Returns a pager to iterate over the root category.
     *
     * @param int   $page
     * @param int   $limit
     * @param array $criteria
     *
     * @return PagerInterface
     */
    public function getRootCategoriesPager($page = 1, $limit = 25, $criteria = []);

    /**
     * @param int   $categoryId
     * @param int   $page
     * @param int   $limit
     * @param array $criteria
     *
     * @return PagerInterface
     */
    public function getSubCategoriesPager($categoryId, $page = 1, $limit = 25, $criteria = []);

    /**
     * @return CategoryInterface
     */
    public function getRootCategoryWithChildren(CategoryInterface $category);

    /**
     * @param bool $loadChildren
     *
     * @return CategoryInterface[]
     */
    public function getRootCategories($loadChildren = true);

    /**
     * @param ContextInterface|string $context
     *
     * @return CategoryInterface[]
     */
    public function getCategories($context = null);

    /**
     * @param ContextInterface|string $context
     *
     * @return CategoryInterface
     */
    public function getRootCategory($context = null);

    /**
     * @param ContextInterface $context
     *
     * @return CategoryInterface[]
     */
    public function getRootCategoriesForContext(?ContextInterface $context = null);

    /**
     * @param bool|true $loadChildren
     *
     * @return CategoryInterface[]
     */
    public function getAllRootCategories($loadChildren = true);

    /**
     * @param bool|true $loadChildren
     *
     * @return array
     */
    public function getRootCategoriesSplitByContexts($loadChildren = true);
}
