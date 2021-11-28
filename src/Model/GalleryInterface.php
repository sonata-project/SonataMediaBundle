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
 * @phpstan-template T of GalleryItemInterface
 */
interface GalleryInterface
{
    public function __toString(): string;

    /**
     * @return int|string|null
     */
    public function getId();

    public function setName(?string $name): void;

    public function getName(): ?string;

    public function setContext(?string $context): void;

    public function getContext(): ?string;

    public function setEnabled(bool $enabled): void;

    public function getEnabled(): bool;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setDefaultFormat(string $defaultFormat): void;

    public function getDefaultFormat(): string;

    /**
     * @param iterable<int, GalleryItemInterface> $galleryItems
     *
     * @phpstan-param iterable<int, T> $galleryItems
     */
    public function setGalleryItems(iterable $galleryItems): void;

    /**
     * @return Collection<int, GalleryItemInterface>
     *
     * @phpstan-return Collection<int, T>
     */
    public function getGalleryItems(): Collection;

    /**
     * @phpstan-param T $galleryItem
     */
    public function addGalleryItem(GalleryItemInterface $galleryItem): void;

    /**
     * @phpstan-param T $galleryItem
     */
    public function removeGalleryItem(GalleryItemInterface $galleryItem): void;

    /**
     * Reorders $galleryItems based on their position.
     */
    public function reorderGalleryItems(): void;
}
