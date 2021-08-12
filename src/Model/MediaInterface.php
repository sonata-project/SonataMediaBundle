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

use Imagine\Image\Box;
use Sonata\ClassificationBundle\Model\CategoryInterface;

/**
 * @method void                   setCategory(?CategoryInterface $category)
 * @method CategoryInterface|null getCategory()
 * @method string                 __toString()
 */
interface MediaInterface
{
    public const STATUS_OK = 1;
    public const STATUS_SENDING = 2;
    public const STATUS_PENDING = 3;
    public const STATUS_ERROR = 4;
    public const STATUS_ENCODING = 5;

    // NEXY_MAJOR: Uncomment this method.
    // public function __toString();

    public const MISSING_BINARY_REFERENCE = 'missing_binary_content';

    // NEXY_MAJOR: Uncomment this method.
    // public function __toString(): string;

    /**
     * @param mixed $binaryContent
     */
    public function setBinaryContent($binaryContent);

    /**
     * @return mixed
     */
    public function getBinaryContent();

    /**
     * Reset the binary content.
     */
    public function resetBinaryContent();

    /**
     * @param string $name
     * @param mixed  $default
     */
    public function getMetadataValue($name, $default = null);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setMetadataValue($name, $value);

    /**
     * Remove a named data from the metadata.
     *
     * @param string $name
     */
    public function unsetMetadataValue($name);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string|null $name
     */
    public function getName();

    /**
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return string|null $description
     */
    public function getDescription();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName);

    /**
     * @return string|null $providerName
     */
    public function getProviderName();

    /**
     * @param int $providerStatus
     */
    public function setProviderStatus($providerStatus);

    /**
     * @return int|null $providerStatus
     */
    public function getProviderStatus();

    /**
     * @param string $providerReference
     */
    public function setProviderReference($providerReference);

    /**
     * @return string|null $providerReference
     */
    public function getProviderReference();

    public function setProviderMetadata(array $providerMetadata = []);

    /**
     * @return array $providerMetadata
     */
    public function getProviderMetadata();

    /**
     * @param int $width
     */
    public function setWidth($width);

    /**
     * @return int|null $width
     */
    public function getWidth();

    /**
     * @param int $height
     */
    public function setHeight($height);

    /**
     * @return int|null $height
     */
    public function getHeight();

    /**
     * @param float $length
     */
    public function setLength($length);

    /**
     * @return float|null $length
     */
    public function getLength();

    /**
     * @param string $copyright
     */
    public function setCopyright($copyright);

    /**
     * @return string|null $copyright
     */
    public function getCopyright();

    /**
     * @param string $authorName
     */
    public function setAuthorName($authorName);

    /**
     * @return string|null $authorName
     */
    public function getAuthorName();

    /**
     * @param string $context
     */
    public function setContext($context);

    /**
     * @return string|null $context
     */
    public function getContext();

    /**
     * @param bool $cdnIsFlushable
     */
    public function setCdnIsFlushable($cdnIsFlushable);

    /**
     * @return bool $cdnIsFlushable
     */
    public function getCdnIsFlushable();

    /**
     * @param string $cdnFlushIdentifier
     */
    public function setCdnFlushIdentifier($cdnFlushIdentifier);

    /**
     * @return string|null $cdnFlushIdentifier
     */
    public function getCdnFlushIdentifier();

    /**
     * @param \DateTime $cdnFlushAt
     */
    public function setCdnFlushAt(?\DateTime $cdnFlushAt = null);

    /**
     * @return \DateTime|null $cdnFlushAt
     */
    public function getCdnFlushAt();

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return \DateTime|null $updatedAt
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * @return \DateTime|null $createdAt
     */
    public function getCreatedAt();

    /**
     * @param string $contentType
     */
    public function setContentType($contentType);

    /**
     * @return string|null $contentType
     */
    public function getContentType();

    /**
     * @return string|null
     */
    public function getExtension();

    /**
     * @param int $size
     */
    public function setSize($size);

    /**
     * @return int|null $size
     */
    public function getSize();

    /**
     * @param int $cdnStatus
     */
    public function setCdnStatus($cdnStatus);

    /**
     * @return int|null $cdnStatus
     */
    public function getCdnStatus();

    /**
     * @return Box
     */
    public function getBox();

    /**
     * @param GalleryHasMediaInterface[] $galleryHasMedias
     */
    public function setGalleryHasMedias($galleryHasMedias);

    /**
     * @return GalleryHasMediaInterface[]
     */
    public function getGalleryHasMedias();

    /**
     * @return string
     */
    public function getPreviousProviderReference();

    // NEXT_MAJOR: Uncomment this method.
    // public function setCategory(?CategoryInterface $category);

    // NEXT_MAJOR: Uncomment this method.
    // public function getCategory(): ?CategoryInterface;
}
