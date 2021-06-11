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

interface GalleryInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return int
     */
    public function getId();

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
     *
     * @return void
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
    public function setUpdatedAt(?\DateTimeInterface $updatedAt = null);

    /**
     * Get updated_at.
     *
     * @return \DateTimeInterface|null $updatedAt
     */
    public function getUpdatedAt();

    /**
     * Set created_at.
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt = null);

    /**
     * Get created_at.
     *
     * @return \DateTimeInterface|null $createdAt
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
     * @param Collection<array-key, GalleryItemInterface> $galleryItems
     */
    public function setGalleryItems($galleryItems);

    /**
     * @return Collection<array-key, GalleryItemInterface>
     */
    public function getGalleryItems();

    /**
     * @return void
     */
    public function addGalleryItem(GalleryItemInterface $galleryItem);

    public function removeGalleryItem(GalleryItemInterface $galleryItem);
}
