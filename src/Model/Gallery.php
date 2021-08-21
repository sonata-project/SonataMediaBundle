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
 * NEXT_MAJOR: Remove the `GalleryMediaCollectionInterface` implementation.
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
     * @deprecated since sonata-project/media-bundle 3.x. Use `$galleryItems` instead.
     *
     * @var Collection<int, GalleryHasMediaInterface>
     */
    protected $galleryHasMedias;

    /**
     * @var Collection<int, GalleryItemInterface>
     */
    protected $galleryItems;

    public function __toString()
    {
        return $this->getName() ?? '-';
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `setGalleryItems()` instead.
     */
    public function setGalleryHasMedias($galleryHasMedias)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "setGalleryItems()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->galleryHasMedias = new ArrayCollection();

        foreach ($galleryHasMedias as $galleryHasMedia) {
            $this->addGalleryHasMedia($galleryHasMedia);
        }
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `getGalleryItems()` instead.
     */
    public function getGalleryHasMedias()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "getGalleryItems()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->galleryHasMedias;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `addGalleryItem()` instead.
     */
    public function addGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "addGalleryItem()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $galleryHasMedia->setGallery($this);

        $this->galleryHasMedias[] = $galleryHasMedia;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `removeGalleryItem()` instead.
     */
    public function removeGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "removeGalleryItem()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->galleryHasMedias->removeElement($galleryHasMedia);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `addGalleryItem()` instead.
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia)
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "addGalleryItem()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $this->addGalleryHasMedia($galleryHasMedia);
    }

    public function setGalleryItems(iterable $galleryItems): void
    {
        $this->galleryItems = new ArrayCollection();

        foreach ($galleryItems as $galleryItem) {
            $this->addGalleryItem($galleryItem);
        }
    }

    public function getGalleryItems(): Collection
    {
        return $this->galleryItems;
    }

    public function addGalleryItem(GalleryItemInterface $galleryItem): void
    {
        $galleryItem->setGallery($this);

        $this->galleryItems[] = $galleryItem;
    }

    public function removeGalleryItem(GalleryItemInterface $galleryItem): void
    {
        if ($this->galleryItems->contains($galleryItem)) {
            $this->galleryItems->removeElement($galleryItem);
        }
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Reorders $galleryHasMedia items based on their position.
     *
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `reorderGalleryItems()` instead.
     */
    public function reorderGalleryHasMedia()
    {
        @trigger_error(sprintf(
            'The "%s()" method is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "reorderGalleryItems()" method instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        $galleryHasMedias = $this->getGalleryHasMedias();

        if ($galleryHasMedias instanceof \IteratorAggregate) {
            // reorder
            $iterator = $galleryHasMedias->getIterator();

            $iterator->uasort(static function (GalleryHasMediaInterface $a, GalleryHasMediaInterface $b): int {
                return $a->getPosition() <=> $b->getPosition();
            });

            $this->setGalleryHasMedias($iterator);
        }
    }

    public function reorderGalleryItems(): void
    {
        $iterator = $this->getGalleryItems()->getIterator();

        if (!$iterator instanceof \ArrayIterator) {
            throw new \TypeError(sprintf(
                'The gallery %s cannot be reordered, "%s::$galleryItems" MUST implement "%s".',
                $this->getId(),
                __CLASS__,
                \ArrayIterator::class
            ));
        }

        $iterator->uasort(static function (GalleryItemInterface $a, GalleryItemInterface $b): int {
            return $a->getPosition() <=> $b->getPosition();
        });

        $this->setGalleryItems(new ArrayCollection(iterator_to_array($iterator)));
    }
}
