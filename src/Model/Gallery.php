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

abstract class Gallery implements GalleryInterface
{
    /**
     * @var string|null
     */
    protected $context;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt;

    /**
     * @var string|null
     */
    protected $defaultFormat = MediaProviderInterface::FORMAT_REFERENCE;

    /**
     * @var Collection<int|string, GalleryItemInterface>
     *
     * @phpstan-var Collection<array-key, GalleryItemInterface>
     */
    protected $galleryItems;

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

    public function setDefaultFormat(?string $defaultFormat): void
    {
        $this->defaultFormat = $defaultFormat;
    }

    public function getDefaultFormat(): ?string
    {
        return $this->defaultFormat;
    }

    public function setGalleryItems(Collection $galleryItems): void
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

    public function reorderGalleryItems(): void
    {
        $iterator = $this->getGalleryItems()->getIterator();

        if (!$iterator instanceof \ArrayIterator) {
            throw new \RuntimeException(sprintf('The gallery %s cannot be reordered, $galleryItems should implement %s', $this->getId(), \ArrayIterator::class));
        }

        $iterator->uasort(static function (GalleryItemInterface $a, GalleryItemInterface $b): int {
            return $a->getPosition() <=> $b->getPosition();
        });

        $this->setGalleryItems(new ArrayCollection(iterator_to_array($iterator)));
    }
}
