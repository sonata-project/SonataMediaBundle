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

use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\Doctrine\Model\ManagerInterface;

interface GalleryManagerInterface extends ManagerInterface
{
    public function getPager(array $criteria, int $page, int $limit = 10, array $sort = []): PagerInterface;
}
