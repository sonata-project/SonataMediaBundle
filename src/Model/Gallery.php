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
 * @phpstan-template T of GalleryItemInterface
 * @phpstan-implements GalleryInterface<T>
 */
abstract class Gallery implements GalleryInterface, \Stringable
{
    protected ?string $context = null;

    protected ?string $name = null;

    protected bool $enabled = false;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?\DateTimeInterface $createdAt = null;

    protected string $defaultFormat = MediaProviderInterface::FORMAT_REFERENCE;

    /**
     * @var Collection<int, GalleryItemInterface>
     *
     * @phpstan-var Collection<int, T>
     */
    protected Collection $galleryItems;

    public function __construct()
    {
        $this->galleryItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? '-';
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
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

    public function setDefaultFormat(string $defaultFormat): void
    {
        $this->defaultFormat = $defaultFormat;
    }

    public function getDefaultFormat(): string
    {
        return $this->defaultFormat;
    }

    public function setGalleryItems(iterable $galleryItems): void
    {
        $this->galleryItems->clear();

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

    /**
     * @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/9449
     */
    public function reorderGalleryItems(): void
    {
        $iterator = $this->getGalleryItems()->getIterator();

        if (!$iterator instanceof \ArrayIterator) {
            throw new \RuntimeException(\sprintf(
                'The gallery %s cannot be reordered, $galleryItems should implement %s',
                $this->getId() ?? '',
                \ArrayIterator::class
            ));
        }

        $iterator->uasort(
            static fn (GalleryItemInterface $a, GalleryItemInterface $b): int => $a->getPosition() <=> $b->getPosition()
        );

        $this->setGalleryItems($iterator);
    }
}
