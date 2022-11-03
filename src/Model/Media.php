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
use Imagine\Image\Box;
use Sonata\ClassificationBundle\Model\CategoryInterface;

abstract class Media implements MediaInterface
{
    protected ?string $name = null;

    protected ?string $description = null;

    protected bool $enabled = false;

    protected ?string $providerName = null;

    protected ?int $providerStatus = null;

    protected ?string $providerReference = null;

    /**
     * @var array<string, mixed>
     */
    protected array $providerMetadata = [];

    protected ?int $width = null;

    protected ?int $height = null;

    protected ?float $length = null;

    protected ?string $copyright = null;

    protected ?string $authorName = null;

    protected ?string $context = null;

    protected ?int $cdnStatus = null;

    protected bool $cdnIsFlushable = false;

    protected ?string $cdnFlushIdentifier = null;

    protected ?\DateTimeInterface $cdnFlushAt = null;

    protected ?\DateTimeInterface $updatedAt = null;

    protected ?\DateTimeInterface $createdAt = null;

    /**
     * @var mixed
     */
    protected $binaryContent;

    protected ?string $previousProviderReference = null;

    protected ?string $contentType = null;

    protected ?int $size = null;

    /**
     * @var Collection<int, GalleryItemInterface>
     */
    protected Collection $galleryItems;

    protected ?CategoryInterface $category = null;

    public function __construct()
    {
        $this->galleryItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'n/a';
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setProviderName(?string $providerName): void
    {
        $this->providerName = $providerName;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    public function setProviderStatus(?int $providerStatus): void
    {
        $this->providerStatus = $providerStatus;
    }

    public function getProviderStatus(): ?int
    {
        return $this->providerStatus;
    }

    public function setProviderReference(?string $providerReference): void
    {
        $this->providerReference = $providerReference;
    }

    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    public function setProviderMetadata(array $providerMetadata = []): void
    {
        $this->providerMetadata = $providerMetadata;
    }

    public function getProviderMetadata(): array
    {
        return $this->providerMetadata;
    }

    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setLength(?float $length): void
    {
        $this->length = $length;
    }

    public function getLength(): ?float
    {
        return $this->length;
    }

    public function setCopyright(?string $copyright): void
    {
        $this->copyright = $copyright;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setAuthorName(?string $authorName): void
    {
        $this->authorName = $authorName;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    public function getContext(): ?string
    {
        return $this->context;
    }

    public function setCdnStatus(?int $cdnStatus): void
    {
        $this->cdnStatus = $cdnStatus;
    }

    public function getCdnStatus(): ?int
    {
        return $this->cdnStatus;
    }

    public function setCdnIsFlushable(bool $cdnIsFlushable): void
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    public function getCdnIsFlushable(): bool
    {
        return $this->cdnIsFlushable;
    }

    public function setCdnFlushIdentifier(?string $cdnFlushIdentifier): void
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    public function getCdnFlushIdentifier(): ?string
    {
        return $this->cdnFlushIdentifier;
    }

    public function setCdnFlushAt(?\DateTimeInterface $cdnFlushAt): void
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    public function getCdnFlushAt(): ?\DateTimeInterface
    {
        return $this->cdnFlushAt;
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

    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getCategory(): ?CategoryInterface
    {
        return $this->category;
    }

    public function setCategory(?CategoryInterface $category = null): void
    {
        $this->category = $category;
    }

    public function setGalleryItems(iterable $galleryItems): void
    {
        $this->galleryItems->clear();

        foreach ($galleryItems as $galleryItem) {
            $this->galleryItems->add($galleryItem);
        }
    }

    public function getGalleryItems(): Collection
    {
        return $this->galleryItems;
    }

    public function getBox(): Box
    {
        return new Box($this->width ?? 0, $this->height ?? 0);
    }

    public function getExtension(): ?string
    {
        $providerReference = $this->getProviderReference();

        if (null === $providerReference) {
            return null;
        }

        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($providerReference, \PATHINFO_EXTENSION));
    }

    public function getPreviousProviderReference(): ?string
    {
        return $this->previousProviderReference;
    }

    public function setBinaryContent($binaryContent): void
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    public function resetBinaryContent(): void
    {
        $this->binaryContent = null;
    }

    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    public function getMetadataValue(string $name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return $metadata[$name] ?? $default;
    }

    public function setMetadataValue(string $name, $value): void
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    public function unsetMetadataValue(string $name): void
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * @return array<int, string>
     */
    public static function getStatusList(): array
    {
        return [
            self::STATUS_OK => 'ok',
            self::STATUS_SENDING => 'sending',
            self::STATUS_PENDING => 'pending',
            self::STATUS_ERROR => 'error',
            self::STATUS_ENCODING => 'encoding',
        ];
    }
}
