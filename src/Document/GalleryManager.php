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

namespace Sonata\MediaBundle\Document;

use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\Doctrine\Document\BaseDocumentManager;
use Sonata\MediaBundle\Model\GalleryManagerInterface;

final class GalleryManager extends BaseDocumentManager implements GalleryManagerInterface
{
    public function getPager(array $criteria, int $page, int $limit = 10, array $sort = []): PagerInterface
    {
        throw new \BadMethodCallException('Not implemented yet.');
    }
}
