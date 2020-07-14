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
use Sonata\ClassificationBundle\Model\CategoryManagerInterface as ManagerInterface;

/**
 * @author Joao Albuquerque <albuquerque.joao.filipe@gmail.com>
 * @author Christian Gripp <mail@core23.de>
 */
final class CategoryManager implements CategoryManagerInterface
{
    /**
     * @var ManagerInterface
     */
    private $categoryManager;

    public function __construct(?ManagerInterface $categoryManager = null)
    {
        $this->categoryManager = $categoryManager;
    }

    public function getRootCategory($context): CategoryInterface
    {
        return $this->categoryManager->getRootCategory($context);
    }

    public function getRootCategories($loadChildren = true): iterable
    {
        return $this->categoryManager->getRootCategories($loadChildren);
    }

    public function find($categoryId): ?CategoryInterface
    {
        return $this->categoryManager->find($categoryId);
    }

    public function findBy(array $criteria): iterable
    {
        return $this->categoryManager->findBy($criteria);
    }

    public function findOneBy(array $criteria): ?CategoryInterface
    {
        return $this->categoryManager->findOneBy($criteria);
    }

    public function create(): CategoryInterface
    {
        return $this->categoryManager->create();
    }

    public function save($category): void
    {
        $this->categoryManager->save($category);
    }
}
