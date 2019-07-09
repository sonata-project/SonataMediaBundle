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
 */
interface CategoryManagerInterface
{
    /**
     * @param string $context
     *
     * @return CategoryInterface
     */
    public function getRootCategory($context);

    /**
     * @param bool|true $loadChildren
     *
     * @return CategoryInterface[]
     */
    public function getRootCategories($loadChildren);

    /**
     * @param int $categoryId
     *
     * @return CategoryInterface[]
     */
    public function find($categoryId);

    /**
     * @return CategoryInterface[]
     */
    public function findBy(array $criteria);

    /**
     * @return CategoryInterface|null
     */
    public function findOneBy(array $criteria);

    /**
     * Create an empty category instance.
     *
     * @return CategoryInterface
     */
    public function create();

    /**
     * Save a category.
     *
     * @param CategoryInterface $category The category to save
     */
    public function save($category);
}
