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

use Sonata\Doctrine\Model\ManagerInterface;

interface CategoryManagerInterface extends ManagerInterface
{
    /**
     * @param string $context
     *
     * @return CategoryInterface
     */
    public function getRootCategory($context);

    /**
     * @param bool $loadChildren
     *
     * @return CategoryInterface[]
     */
    public function getRootCategories($loadChildren = true);
}
