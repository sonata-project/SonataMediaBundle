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

namespace Sonata\MediaBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;

/**
 * @author Joao Albuquerque <albuquerque.joao.filipe@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 *
 * @phpstan-template T of CategoryInterface
 */
interface CategoryManagerInterface
{
    /**
     * @param string $context
     *
     * @return CategoryInterface|null
     *
     * @phpstan-return T|null
     */
    public function getRootCategory($context);

    /**
     * @param bool|true $loadChildren
     *
     * @return CategoryInterface[]
     *
     * @phpstan-return T[]
     */
    public function getRootCategories($loadChildren);

    /**
     * @param int $categoryId
     *
     * @return CategoryInterface|null
     *
     * @phpstan-return T|null
     */
    public function find($categoryId);

    /**
     * @return CategoryInterface[]
     *
     * @phpstan-return T[]
     */
    public function findBy(array $criteria);

    /**
     * @return CategoryInterface|null
     *
     * @phpstan-return T|null
     */
    public function findOneBy(array $criteria);

    /**
     * Create an empty category instance.
     *
     * @return CategoryInterface
     *
     * @phpstan-return T
     */
    public function create();

    /**
     * Save a category.
     *
     * @param CategoryInterface $category The category to save
     *
     * @phpstan-param T $category
     */
    public function save($category);
}
