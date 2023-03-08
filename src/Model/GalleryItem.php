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

abstract class GalleryItem implements GalleryItemInterface, \Stringable
{
    protected ?MediaInterface $media = null;

    /**
     * @phpstan-var ?GalleryInterface<GalleryItemInterface>
     */
    protected ?GalleryInterface $gallery = null;

    protected int $position = 0;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?\DateTimeInterface $createdAt = null;

    protected bool $enabled = false;

    public function __toString(): string
    {
        return $this->getGallery().' | '.$this->getMedia();
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setGallery(?GalleryInterface $gallery = null): void
    {
        $this->gallery = $gallery;
    }

    public function getGallery(): ?GalleryInterface
    {
        return $this->gallery;
    }

    public function setMedia(?MediaInterface $media = null): void
    {
        $this->media = $media;
    }

    public function getMedia(): ?MediaInterface
    {
        return $this->media;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
}
