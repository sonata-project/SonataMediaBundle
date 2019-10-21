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

namespace Sonata\MediaBundle\PHPCR;

use Sonata\Doctrine\Document\BaseDocumentManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class GalleryManager extends BaseDocumentManager implements GalleryManagerInterface
{
    /**
     * BC Compatibility.
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated Please use save() from now
     */
    public function update(GalleryInterface $gallery)
    {
        parent::save($gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = [])
    {
        throw new \RuntimeException('Not Implemented yet');
    }
}
