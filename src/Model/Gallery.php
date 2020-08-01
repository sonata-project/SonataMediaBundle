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

namespace Sonata\MediaBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

/**
 * NEXT_MAJOR: remove GalleryMediaCollectionInterface interface. Move its content into GalleryInterface.
 */
abstract class Gallery implements GalleryInterface, GalleryMediaCollectionInterface
{
    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $defaultFormat = MediaProviderInterface::FORMAT_REFERENCE;

    /**
     * @var GalleryItemInterface[]|Collection
     */
    protected $galleryItems;

    public function __toString()
    {
        return $this->getName() ?: '-';
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(?\DateTime $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setDefaultFormat($defaultFormat): void
    {
        $this->defaultFormat = $defaultFormat;
    }

    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    public function setGalleryItems($galleryItems): void
    {
        $this->galleryItems = new ArrayCollection();

        foreach ($galleryItems as $galleryItem) {
            $this->addGalleryItem($galleryItem);
        }
    }

    public function getGalleryItems()
    {
        return $this->galleryItems;
    }

    public function addGalleryItem(GalleryItemInterface $galleryItem): void
    {
        $galleryItem->setGallery($this);

        $this->galleryItems[] = $galleryItem;
    }

    public function setContext($context): void
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Reorders $galleryHasMedia items based on their position.
     */
    public function reorderGalleryHasMedia()
    {
        if ($this->getGalleryHasMedias() && $this->getGalleryHasMedias() instanceof \IteratorAggregate) {
            // reorder
            $iterator = $this->getGalleryHasMedias()->getIterator();

            $iterator->uasort(static function ($a, $b) {
                if ($a->getPosition() === $b->getPosition()) {
                    return 0;
                }

                return $a->getPosition() > $b->getPosition() ? 1 : -1;
            });

            $this->setGalleryHasMedias($iterator);
        }
    }
}
