<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

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

    /**
     * @param ManagerInterface $categoryManager
     */
    public function __construct(ManagerInterface $categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootCategory($context)
    {
        return $this->categoryManager->getRootCategory($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getRootCategories($loadChildren = true)
    {
        return $this->categoryManager->getRootCategories($loadChildren);
    }

    /**
     * {@inheritdoc}
     */
    public function find($categoryId)
    {
        return $this->categoryManager->find($categoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return $this->categoryManager->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->categoryManager->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->categoryManager->create();
    }

    /**
     * {@inheritdoc}
     */
    public function save($category)
    {
        $this->categoryManager->save($category);
    }
}
