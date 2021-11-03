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
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getDescription(): ?string
    {
        return $this->description;
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
    public function setProviderName(?string $providerName): void
    {
        $this->providerName = $providerName;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setProviderStatus(?int $providerStatus): void
    {
        $this->providerStatus = $providerStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getProviderStatus(): ?int
    {
        return $this->providerStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setProviderReference(?string $providerReference): void
    {
        $this->providerReference = $providerReference;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getProviderReference(): ?string
    {
        return $this->providerReference;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setProviderMetadata(array $providerMetadata = []): void
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getProviderMetadata(): array
    {
        return $this->providerMetadata;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setWidth(?int $width): void
    {
        $this->width = $width;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setHeight(?int $height): void
    {
        $this->height = $height;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setLength(?float $length): void
    {
        $this->length = $length;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getLength(): ?float
    {
        return $this->length;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setCopyright(?string $copyright): void
    {
        $this->copyright = $copyright;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setAuthorName(?string $authorName): void
    {
        $this->authorName = $authorName;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getAuthorName(): ?string
    {
        return $this->authorName;
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
    public function setCdnStatus(?int $cdnStatus): void
    {
        $this->cdnStatus = $cdnStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCdnStatus(): ?int
    {
        return $this->cdnStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setCdnIsFlushable(bool $cdnIsFlushable): void
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCdnIsFlushable(): bool
    {
        return $this->cdnIsFlushable;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setCdnFlushIdentifier(?string $cdnFlushIdentifier): void
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCdnFlushIdentifier(): ?string
    {
        return $this->cdnFlushIdentifier;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setCdnFlushAt(?\DateTimeInterface $cdnFlushAt): void
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCdnFlushAt(): ?\DateTimeInterface
    {
        return $this->cdnFlushAt;
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
    public function setContentType(?string $contentType): void
    {
        $this->contentType = $contentType;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setSize(?int $size): void
    {
        $this->size = $size;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getCategory(): ?object
    {
        return $this->category;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
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

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getBox(): Box
    {
        return new Box($this->width ?? 0, $this->height ?? 0);
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getExtension(): ?string
    {
        $providerReference = $this->getProviderReference();

        if (null === $providerReference) {
            return null;
        }

        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($providerReference, \PATHINFO_EXTENSION));
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getPreviousProviderReference(): ?string
    {
        return $this->previousProviderReference;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setBinaryContent($binaryContent): void
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function resetBinaryContent(): void
    {
        $this->binaryContent = null;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function getMetadataValue(string $name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return $metadata[$name] ?? $default;
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
    public function setMetadataValue(string $name, $value): void
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * @final since sonata-project/media-bundle 3.x
     */
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
