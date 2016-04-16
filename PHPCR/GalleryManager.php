<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\PHPCR;

use Sonata\CoreBundle\Model\BaseDocumentManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;

class GalleryManager extends BaseDocumentManager implements GalleryManagerInterface
{
    /**
     * BC Compatibility.
     *
     * @deprecated Please use save() from now
     *
     * @param GalleryInterface $gallery
     */
    public function update(GalleryInterface $gallery)
    {
        parent::save($gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = array())
    {
        throw new \RuntimeException('Not Implemented yet');
    }
}
