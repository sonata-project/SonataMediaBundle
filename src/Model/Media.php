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

    /**
     * @var CategoryInterface|null
     */
    protected ?object $category = null;

    public function __construct()
    {
        $this->galleryItems = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? 'n/a';
    }

    final public function setName(?string $name): void
    {
        $this->name = $name;
    }

    final public function getName(): ?string
    {
        return $this->name;
    }

    final public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    final public function getDescription(): ?string
    {
        return $this->description;
    }

    final public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    final public function getEnabled(): bool
    {
        return $this->enabled;
    }

    final public function setProviderName(?string $providerName): void
    {
        $this->providerName = $providerName;
    }

    final public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    final public function setProviderStatus(?int $providerStatus): void
    {
        $this->providerStatus = $providerStatus;
    }

    final public function getProviderStatus(): ?int
    {
        return $this->providerStatus;
    }

    final public function setProviderReference(?string $providerReference): void
    {
        $this->providerReference = $providerReference;
    }

    final public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    final public function setProviderMetadata(array $providerMetadata = []): void
    {
        $this->providerMetadata = $providerMetadata;
    }

    final public function getProviderMetadata(): array
    {
        return $this->providerMetadata;
    }

    final public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    final public function getWidth(): ?int
    {
        return $this->width;
    }

    final public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    final public function getHeight(): ?int
    {
        return $this->height;
    }

    final public function setLength(?float $length): void
    {
        $this->length = $length;
    }

    final public function getLength(): ?float
    {
        return $this->length;
    }

    final public function setCopyright(?string $copyright): void
    {
        $this->copyright = $copyright;
    }

    final public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    final public function setAuthorName(?string $authorName): void
    {
        $this->authorName = $authorName;
    }

    final public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    final public function setContext(?string $context): void
    {
        $this->context = $context;
    }

    final public function getContext(): ?string
    {
        return $this->context;
    }

    final public function setCdnStatus(?int $cdnStatus): void
    {
        $this->cdnStatus = $cdnStatus;
    }

    final public function getCdnStatus(): ?int
    {
        return $this->cdnStatus;
    }

    final public function setCdnIsFlushable(bool $cdnIsFlushable): void
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    final public function getCdnIsFlushable(): bool
    {
        return $this->cdnIsFlushable;
    }

    final public function setCdnFlushIdentifier(?string $cdnFlushIdentifier): void
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    final public function getCdnFlushIdentifier(): ?string
    {
        return $this->cdnFlushIdentifier;
    }

    final public function setCdnFlushAt(?\DateTimeInterface $cdnFlushAt): void
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    final public function getCdnFlushAt(): ?\DateTimeInterface
    {
        return $this->cdnFlushAt;
    }

    final public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    final public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    final public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    final public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    final public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    final public function getContentType(): ?string
    {
        return $this->contentType;
    }

    final public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    final public function getSize(): ?int
    {
        return $this->size;
    }

    final public function getCategory(): ?object
    {
        return $this->category;
    }

    final public function setCategory(?object $category = null): void
    {
        $this->category = $category;
    }

    final public function setGalleryItems(Collection $galleryItems): void
    {
        $this->galleryItems = $galleryItems;
    }

    final public function getGalleryItems(): Collection
    {
        return $this->galleryItems;
    }

    final public function getBox(): Box
    {
        return new Box($this->width ?? 0, $this->height ?? 0);
    }

    final public function getExtension(): ?string
    {
        $providerReference = $this->getProviderReference();

        if (null === $providerReference) {
            return null;
        }

        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($providerReference, \PATHINFO_EXTENSION));
    }

    final public function getPreviousProviderReference(): ?string
    {
        return $this->previousProviderReference;
    }

    final public function setBinaryContent($binaryContent): void
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    final public function resetBinaryContent(): void
    {
        $this->binaryContent = null;
    }

    final public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    final public function getMetadataValue(string $name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return $metadata[$name] ?? $default;
    }

    final public function setMetadataValue(string $name, $value): void
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    final public function unsetMetadataValue(string $name): void
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
