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

use Doctrine\Common\Collections\Collection;

/**
 * @method int|string|null getId()
 * @method void            setGalleryItems(Collection $galleryItems)
 * @method Collection      getGalleryItems()
 * @method void            addGalleryItem(GalleryItemInterface $galleryItem)
 * @method void            removeGalleryItem(GalleryItemInterface $galleryItem)
 * @method void            reorderGalleryItems()
 */
interface GalleryInterface
{
    /**
     * @return string
     */
    public function __toString();

    // NEXT_MAJOR: Uncomment this method.
    // /**
    // * @return int|string|null
    // */
    // public function getId();

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getContext();

    /**
     * @param string $context
     */
    public function setContext($context);

    /**
     * Get name.
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set updated_at.
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * Get updated_at.
     *
     * @return \DateTime|null $updatedAt
     */
    public function getUpdatedAt();

    /**
     * Set created_at.
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * Get created_at.
     *
     * @return \DateTime|null $createdAt
     */
    public function getCreatedAt();

    /**
     * @param string $defaultFormat
     */
    public function setDefaultFormat($defaultFormat);

    /**
     * @return string
     */
    public function getDefaultFormat();

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `setGalleryItems()` instead.
     *
     * @param iterable<GalleryHasMediaInterface> $galleryHasMedias
     */
    public function setGalleryHasMedias($galleryHasMedias);

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `getGalleryItems()` instead.
     *
     * @return Collection<GalleryHasMediaInterface>
     */
    public function getGalleryHasMedias();

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.x. Use `addGalleryItem()` instead.
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia);

    // NEXT_MAJOR: Uncomment this method.
    // /**
    //  * @param iterable<int, GalleryItemInterface> $galleryItems
    //  */
    // public function setGalleryItems(iterable $galleryItems): void;

    // NEXT_MAJOR: Uncomment this method.
    // /**
    //  * @return Collection<int, GalleryItemInterface>
    //  */
    // public function getGalleryItems(): Collection;

    // NEXT_MAJOR: Uncomment this method.
    // public function addGalleryItem(GalleryItemInterface $galleryItem): void;

    // NEXT_MAJOR: Uncomment this method.
    // public function removeGalleryItem(GalleryItemInterface $galleryItem): void;

    // NEXT_MAJOR: Uncomment this method.
    // /**
    //  * Reorders $galleryItems based on their position.
    //  */
    // public function reorderGalleryItems(): void;
}
