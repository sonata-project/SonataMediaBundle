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
 * NEXT_MAJOR: Replace the `reorderGalleryHasMedia()` method with `reorderGalleryItems()`.
 *
 * @method void reorderGalleryHasMedia()
 *
 * @phpstan-template T of GalleryHasMediaInterface
 */
interface GalleryInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext($context);

    /**
     * @return string|null
     */
    public function getContext();

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @return void
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt();

    /**
     * @return void
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt();

    /**
     * @param string $defaultFormat
     *
     * @return void
     */
    public function setDefaultFormat($defaultFormat);

    /**
     * @return string|null
     */
    public function getDefaultFormat();

    /**
     * @param iterable<GalleryHasMediaInterface> $galleryHasMedias
     *
     * @return void
     *
     * @phpstan-param iterable<T> $galleryHasMedias
     */
    public function setGalleryHasMedias($galleryHasMedias);

    /**
     * @return Collection<int, GalleryHasMediaInterface>
     *
     * @phpstan-return Collection<int, T>
     */
    public function getGalleryHasMedias();

    /**
     * @deprecated implement addGalleryHasMedia method instead, it will be provided with the next major release
     * NEXT_MAJOR: remove this method
     *
     * @return void
     *
     * @phpstan-param T $galleryHasMedia
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia);
}
