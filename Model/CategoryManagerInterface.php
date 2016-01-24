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

interface CategoryManagerInterface
{
    /**
     * @param string $context
     *
     * @return array
     */
    public function getRootCategory($context);

    /**
     * @param bool|true $loadChildren
     *
     * @return array
     */
    public function getRootCategories($loadChildren);

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function find($categoryId);

    /**
     * @param array $criteria
     *
     * @return array
     */
    public function findBy(array $criteria);
}
