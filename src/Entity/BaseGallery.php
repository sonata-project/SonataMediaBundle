<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;

/**
 * Bundle\MediaBundle\Entity\BaseGallery.
 */
abstract class BaseGallery extends Gallery
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->galleryHasMedias = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = $galleryHasMedias;
        foreach ($this->galleryHasMedias as $galleryHasMedia) {
            $this->addGalleryHasMedias($galleryHasMedia);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia)
    {
        $galleryHasMedia->setGallery($this);
        if (!$this->galleryHasMedias->contains($galleryHasMedia)) {
            $this->galleryHasMedias->add($galleryHasMedia);
        }
    }

    /**
     * Pre Persist method.
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Pre Update method.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}
