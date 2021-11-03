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
    protected ?string $context = null;

    protected ?string $name = null;

    protected bool $enabled = false;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?\DateTimeInterface $createdAt = null;

    protected string $defaultFormat = MediaProviderInterface::FORMAT_REFERENCE;

    /**
     * @var Collection<int, GalleryItemInterface>
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

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getContext(): ?string
    {
        return $this->context;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setDefaultFormat(string $defaultFormat): void
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getDefaultFormat(): string
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
            throw new \RuntimeException(sprintf(
                'The gallery %s cannot be reordered, $galleryItems should implement %s',
                $this->getId() ?? '',
                \ArrayIterator::class
            ));
        }

        $iterator->uasort(static function (GalleryItemInterface $a, GalleryItemInterface $b): int {
            return $a->getPosition() <=> $b->getPosition();
        });

        $this->setGalleryItems(new ArrayCollection(iterator_to_array($iterator)));
    }
}
