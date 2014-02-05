<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Entity;

use Sonata\CoreBundle\Entity\DoctrineBaseManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;

class GalleryManager extends DoctrineBaseManager implements GalleryManagerInterface
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
