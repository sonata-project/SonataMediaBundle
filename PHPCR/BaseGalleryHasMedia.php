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

use Sonata\MediaBundle\Model\GalleryHasMedia;
use Sonata\MediaBundle\Model\GalleryInterface;

abstract class BaseGalleryHasMedia extends GalleryHasMedia
{
    /**
     * @var string
     */
    protected $nodename;

    /**
     * Set node name
     *
     * @param string $nodename
     */
    public function setNodename($nodename)
    {
        $this->nodename = $nodename;
    }

    /**
     * Get node name
     *
     * @return string
     */
    public function getNodename()
    {
        return $this->nodename;
    }

    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
