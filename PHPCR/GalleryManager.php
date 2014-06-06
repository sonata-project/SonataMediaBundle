<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace Sonata\MediaBundle\PHPCR;

use Sonata\CoreBundle\Model\BaseDocumentManager;
use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;

class GalleryManager extends BaseDocumentManager
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
}
