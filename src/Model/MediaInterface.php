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

interface MediaInterface
{
    public const STATUS_OK = 1;
    public const STATUS_SENDING = 2;
    public const STATUS_PENDING = 3;
    public const STATUS_ERROR = 4;
    public const STATUS_ENCODING = 5;

    public const MISSING_BINARY_REFERENCE = 'missing_binary_content';

    public function __toString(): string;

    /**
     * @return int|string|null
     */
    public function getId();

    public function setName(?string $name): void;

    public function getName(): ?string;

    public function setDescription(?string $description): void;

    public function getDescription(): ?string;

    public function setEnabled(bool $enabled): void;

    public function getEnabled(): bool;

    public function setProviderName(?string $providerName): void;

    public function getProviderName(): ?string;

    public function setProviderStatus(?int $providerStatus): void;

    public function getProviderStatus(): ?int;

    public function setProviderReference(?string $providerReference): void;

    public function getProviderReference(): ?string;

    /**
     * @param array<string, mixed> $providerMetadata
     */
    public function setProviderMetadata(array $providerMetadata = []): void;

    /**
     * @return array<string, mixed>
     */
    public function getProviderMetadata(): array;

    public function setWidth(?int $width): void;

    public function getWidth(): ?int;

    public function setHeight(?int $height): void;

    public function getHeight(): ?int;

    public function setLength(?float $length): void;

    public function getLength(): ?float;

    public function setCopyright(?string $copyright): void;

    public function getCopyright(): ?string;

    public function setAuthorName(?string $authorName): void;

    public function getAuthorName(): ?string;

    public function setContext(?string $context): void;

    public function getContext(): ?string;

    public function setCdnStatus(?int $cdnStatus): void;

    public function getCdnStatus(): ?int;

    public function setCdnIsFlushable(bool $cdnIsFlushable): void;

    public function getCdnIsFlushable(): bool;

    public function setCdnFlushIdentifier(?string $cdnFlushIdentifier): void;

    public function getCdnFlushIdentifier(): ?string;

    public function setCdnFlushAt(?\DateTimeInterface $cdnFlushAt): void;

    public function getCdnFlushAt(): ?\DateTimeInterface;

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void;

    public function getUpdatedAt(): ?\DateTimeInterface;

    public function setCreatedAt(?\DateTimeInterface $createdAt): void;

    public function getCreatedAt(): ?\DateTimeInterface;

    public function setContentType(?string $contentType): void;

    public function getContentType(): ?string;

    public function setSize(?int $size): void;

    public function getSize(): ?int;

    /**
     * @return CategoryInterface|null
     */
    public function getCategory(): ?object;

    /**
     * @param CategoryInterface|null $category
     */
    public function setCategory(?object $category = null): void;

    /**
     * @param iterable<int, GalleryItemInterface> $galleryItems
     */
    public function setGalleryItems(iterable $galleryItems): void;

    /**
     * @return Collection<int, GalleryItemInterface>
     */
    public function getGalleryItems(): Collection;

    public function getBox(): Box;

    public function getExtension(): ?string;

    public function getPreviousProviderReference(): ?string;

    public function setBinaryContent(mixed $binaryContent): void;

    /**
     * @return mixed
     */
    public function getBinaryContent();

    public function resetBinaryContent(): void;

    /**
     * @return mixed
     */
    public function getMetadataValue(string $name, mixed $default = null);

    public function setMetadataValue(string $name, mixed $value): void;

    public function unsetMetadataValue(string $name): void;
}
