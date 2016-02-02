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

use Sonata\CoreBundle\Model\BaseManager;

class CategoryManager implements CategoryManagerInterface
{
    private $categoryManager;

    /**
     * @param BaseManager $categoryManager
     */
    public function __construct(BaseManager $categoryManager = null)
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
}
