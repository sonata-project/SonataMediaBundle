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
     *
     * @return void
     */
    public function setBinaryContent($binaryContent);

    /**
     * @return mixed
     */
    public function getBinaryContent();

    /**
     * Reset the binary content.
     *
     * @return void
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
     *
     * @return void
     */
    public function setMetadataValue($name, $value);

    /**
     * Remove a named data from the metadata.
     *
     * @param string $name
     *
     * @return void
     */
    public function unsetMetadataValue($name);

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param string $providerName
     *
     * @return void
     */
    public function setProviderName($providerName);

    /**
     * @return string|null
     */
    public function getProviderName();

    /**
     * @param int $providerStatus
     *
     * @return void
     */
    public function setProviderStatus($providerStatus);

    /**
     * @return int|null
     */
    public function getProviderStatus();

    /**
     * @param string $providerReference
     *
     * @return void
     */
    public function setProviderReference($providerReference);

    /**
     * @return string|null
     */
    public function getProviderReference();

    /**
     * @return void
     */
    public function setProviderMetadata(array $providerMetadata = []);

    /**
     * @return array
     */
    public function getProviderMetadata();

    /**
     * @param int $width
     *
     * @return void
     */
    public function setWidth($width);

    /**
     * @return int|null
     */
    public function getWidth();

    /**
     * @param int $height
     *
     * @return void
     */
    public function setHeight($height);

    /**
     * @return int|null
     */
    public function getHeight();

    /**
     * @param float $length
     *
     * @return void
     */
    public function setLength($length);

    /**
     * @return float|null
     */
    public function getLength();

    /**
     * @param string $copyright
     *
     * @return void
     */
    public function setCopyright($copyright);

    /**
     * @return string|null
     */
    public function getCopyright();

    /**
     * @param string $authorName
     *
     * @return void
     */
    public function setAuthorName($authorName);

    /**
     * @return string|null
     */
    public function getAuthorName();

    /**
     * @param string $context
     *
     * @return void
     */
    public function setContext($context);

    /**
     * @return string|null
     */
    public function getContext();

    /**
     * @param bool $cdnIsFlushable
     *
     * @return void
     */
    public function setCdnIsFlushable($cdnIsFlushable);

    /**
     * @return bool
     */
    public function getCdnIsFlushable();

    /**
     * @param string $cdnFlushIdentifier
     *
     * @return void
     */
    public function setCdnFlushIdentifier($cdnFlushIdentifier);

    /**
     * @return string|null
     */
    public function getCdnFlushIdentifier();

    /**
     * @param \DateTime $cdnFlushAt
     *
     * @return void
     */
    public function setCdnFlushAt(?\DateTime $cdnFlushAt = null);

    /**
     * @return \DateTime|null
     */
    public function getCdnFlushAt();

    /**
     * @param \DateTime $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null);

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $createdAt
     *
     * @return void
     */
    public function setCreatedAt(?\DateTime $createdAt = null);

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt();

    /**
     * @param string $contentType
     *
     * @return void
     */
    public function setContentType($contentType);

    /**
     * @return string|null
     */
    public function getContentType();

    /**
     * @return string|null
     */
    public function getExtension();

    /**
     * @param int $size
     *
     * @return void
     */
    public function setSize($size);

    /**
     * @return int|null
     */
    public function getSize();

    /**
     * @param int $cdnStatus
     *
     * @return void
     */
    public function setCdnStatus($cdnStatus);

    /**
     * @return int|null
     */
    public function getCdnStatus();

    /**
     * @return Box
     */
    public function getBox();

    /**
     * @param GalleryHasMediaInterface[] $galleryHasMedias
     *
     * @return void
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
