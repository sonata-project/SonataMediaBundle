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
use Imagine\Image\Box;
use Sonata\ClassificationBundle\Model\CategoryInterface;

abstract class Media implements MediaInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string|null
     */
    protected $providerName;

    /**
     * @var int|null
     */
    protected $providerStatus;

    /**
     * @var string|null
     */
    protected $providerReference;

    /**
     * @var array<string, mixed>
     */
    protected $providerMetadata = [];

    /**
     * @var int|null
     */
    protected $width;

    /**
     * @var int|null
     */
    protected $height;

    /**
     * @var float|null
     */
    protected $length;

    /**
     * @var string|null
     */
    protected $copyright;

    /**
     * @var string|null
     */
    protected $authorName;

    /**
     * @var string|null
     */
    protected $context;

    /**
     * @var int|null
     */
    protected $cdnStatus;

    /**
     * @var bool
     */
    protected $cdnIsFlushable = false;

    /**
     * @var string|null
     */
    protected $cdnFlushIdentifier;

    /**
     * @var \DateTimeInterface|null
     */
    protected $cdnFlushAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $updatedAt;

    /**
     * @var \DateTimeInterface|null
     */
    protected $createdAt;

    /**
     * @var mixed
     */
    protected $binaryContent;

    /**
     * @var string|null
     */
    protected $previousProviderReference;

    /**
     * @var string|null
     */
    protected $contentType;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * @var Collection<int|string, GalleryItemInterface>
     *
     * @phpstan-var Collection<array-key, GalleryItemInterface>
     */
    protected $galleryItems;

    /**
     * @var CategoryInterface|null
     */
    protected $category;

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

    public function getCategory(): ?object
    {
        return $this->category;
    }

    public function setCategory(?object $category = null): void
    {
        $this->category = $category;
    }

    public function setGalleryItems(Collection $galleryItems): void
    {
        $this->galleryItems = $galleryItems;
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
