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

use Doctrine\Common\Collections\ArrayCollection;
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

    public function __construct()
    {
        $this->galleryItems = new ArrayCollection();
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

    public function addGalleryItem(GalleryItemInterface $galleryItem): void
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
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryItems();
    }

    /**
     * Pre Update method.
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();

        $this->reorderGalleryItems();
    }
}
