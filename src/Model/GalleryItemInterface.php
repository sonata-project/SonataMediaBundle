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

interface GalleryItemInterface
{
    public function __toString(): string;

    /**
     * @return int|string|object
     */
    public function getId();

    public function setEnabled(bool $enabled): void;

    public function getEnabled(): bool;

    public function setGallery(?GalleryInterface $gallery = null): void;

    public function getGallery(): ?GalleryInterface;

    public function setMedia(?MediaInterface $media = null): void;

    public function getMedia(): ?MediaInterface;

    public function setPosition(int $position): void;

    public function getPosition(): int;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt): void;

    public function getCreatedAt(): ?\DateTimeInterface;
}
