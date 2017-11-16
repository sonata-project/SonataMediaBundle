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

use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryItemInterface;

/**
 * Bundle\MediaBundle\Document\BaseGallery.
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
        $this->galleryItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get universal unique id.
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
    public function addGalleryItem(GalleryItemInterface $galleryItem)
    {
        $galleryItem->setGallery($this);

        // set nodename of GalleryItem
        if (!$galleryItem->getNodename()) {
            $galleryItem->setNodename(
                'media'.($this->galleryItems->count() + 1)
            );
        }

        $this->galleryItems->set($galleryItem->getNodename(), $galleryItem);
    }

    /**
     * Pre persist method.
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryItems();
    }

    /**
     * Pre Update method.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryItems();
    }

    /**
     * Reorders gallery items based on their position.
     */
    public function reorderGalleryItems()
    {
        if ($this->getGalleryItems() && $this->getGalleryItems() instanceof \IteratorAggregate) {
            // reorder
            $iterator = $this->getGalleryItems()->getIterator();

            $iterator->uasort(function ($a, $b) {
                if ($a->getPosition() === $b->getPosition()) {
                    return 0;
                }

                return $a->getPosition() > $b->getPosition() ? 1 : -1;
            });

            $this->setGalleryItems($iterator);
        }
    }
}
