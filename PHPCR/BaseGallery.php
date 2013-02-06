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

use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;

/**
 * Bundle\MediaBundle\Document\BaseGallery
 */
abstract class BaseGallery extends Gallery
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->galleryHasMedias = new \Doctrine\Common\Collections\ArrayCollection;
    }

    /**
     * Get universal unique id
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * {@inheritdoc}
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia)
    {
        $galleryHasMedia->setGallery($this);

        // set nodename of GalleryHasMedia
        if (!$galleryHasMedia->getNodename()) {
            $galleryHasMedia->setNodename(
                'media'.($this->galleryHasMedias->count() + 1)
            );
        }

        $this->galleryHasMedias->set($galleryHasMedia->getNodename(), $galleryHasMedia);
    }

    /**
     * Pre persist method
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryHasMedia();
    }

    /**
     * Pre Update method
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryHasMedia();
    }

    /**
     * Reorders $galleryHasMedia items based on their position
     */
    public function reorderGalleryHasMedia()
    {
        if ($this->getGalleryHasMedias() && $this->getGalleryHasMedias() instanceof \IteratorAggregate) {

            // reorder
            $iterator = $this->getGalleryHasMedias()->getIterator();

            $iterator->uasort(function ($a, $b) {
                if ($a->getPosition() === $b->getPosition()) {
                    return 0;
                }

                return $a->getPosition() > $b->getPosition() ? 1 : -1;
            });

            $this->setGalleryHasMedias($iterator);
        }
    }
}
