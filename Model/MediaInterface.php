<?php

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

interface MediaInterface
{
    const STATUS_OK          = 1;
    const STATUS_SENDING     = 2;
    const STATUS_PENDING     = 3;
    const STATUS_ERROR       = 4;
    const STATUS_ENCODING    = 5;

    const MISSING_BINARY_REFERENCE = 'missing_binary_content';

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
     * @param null   $default
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
     * Set name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string $name
     */
    public function getName();

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description);

    /**
     * Get description.
     *
     * @return string $description
     */
    public function getDescription();

    /**
     * Set enabled.
     *
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * Get enabled.
     *
     * @return bool $enabled
     */
    public function getEnabled();

    /**
     * Set provider_name.
     *
     * @param string $providerName
     */
    public function setProviderName($providerName);

    /**
     * Get provider_name.
     *
     * @return string $providerName
     */
    public function getProviderName();

    /**
     * Set provider_status.
     *
     * @param int $providerStatus
     */
    public function setProviderStatus($providerStatus);

    /**
     * Get provider_status.
     *
     * @return int $providerStatus
     */
    public function getProviderStatus();

    /**
     * Set provider_reference.
     *
     * @param string $providerReference
     */
    public function setProviderReference($providerReference);

    /**
     * Get provider_reference.
     *
     * @return string $providerReference
     */
    public function getProviderReference();

    /**
     * Set provider_metadata.
     *
     * @param array $providerMetadata
     */
    public function setProviderMetadata(array $providerMetadata = array());

    /**
     * Get provider_metadata.
     *
     * @return array $providerMetadata
     */
    public function getProviderMetadata();

    /**
     * Set width.
     *
     * @param int $width
     */
    public function setWidth($width);

    /**
     * Get width.
     *
     * @return int $width
     */
    public function getWidth();

    /**
     * Set height.
     *
     * @param int $height
     */
    public function setHeight($height);

    /**
     * Get height.
     *
     * @return int $height
     */
    public function getHeight();

    /**
     * Set length.
     *
     * @param float $length
     */
    public function setLength($length);

    /**
     * Get length.
     *
     * @return float $length
     */
    public function getLength();

    /**
     * Set copyright.
     *
     * @param string $copyright
     */
    public function setCopyright($copyright);

    /**
     * Get copyright.
     *
     * @return string $copyright
     */
    public function getCopyright();

    /**
     * Set authorName.
     *
     * @param string $authorName
     */
    public function setAuthorName($authorName);

    /**
     * Get authorName.
     *
     * @return string $authorName
     */
    public function getAuthorName();

    /**
     * Set context.
     *
     * @param string $context
     */
    public function setContext($context);

    /**
     * Get context.
     *
     * @return string $context
     */
    public function getContext();

    /**
     * Set cdnIsFlushable.
     *
     * @param bool $cdnIsFlushable
     */
    public function setCdnIsFlushable($cdnIsFlushable);

    /**
     * Get cdn_is_flushable.
     *
     * @return bool $cdnIsFlushable
     */
    public function getCdnIsFlushable();

    /**
     * Set cdn_flush_identifier.
     *
     * @param bool $cdnFlushIdentifier
     */
    public function setCdnFlushIdentifier($cdnFlushIdentifier);

    /**
     * Get cdn_flush_identifier.
     *
     * @return string $cdnFlushIdentifier
     */
    public function getCdnFlushIdentifier();

    /**
     * Set cdn_flush_at.
     *
     * @param \Datetime $cdnFlushAt
     */
    public function setCdnFlushAt(\Datetime $cdnFlushAt = null);

    /**
     * Get cdn_flush_at.
     *
     * @return \Datetime $cdnFlushAt
     */
    public function getCdnFlushAt();

    /**
     * Set updated_at.
     *
     * @param \Datetime $updatedAt
     */
    public function setUpdatedAt(\Datetime $updatedAt = null);

    /**
     * Get updated_at.
     *
     * @return \Datetime $updatedAt
     */
    public function getUpdatedAt();

    /**
     * Set created_at.
     *
     * @param \Datetime $createdAt
     */
    public function setCreatedAt(\Datetime $createdAt = null);

    /**
     * Get created_at.
     *
     * @return \Datetime $createdAt
     */
    public function getCreatedAt();

    /**
     * Set content_type.
     *
     * @param string $contentType
     */
    public function setContentType($contentType);

    /**
     * @return string
     */
    public function getExtension();

    /**
     * Get content_type.
     *
     * @return string $contentType
     */
    public function getContentType();

    /**
     * Set size.
     *
     * @param int $size
     */
    public function setSize($size);

    /**
     * Get size.
     *
     * @return int $size
     */
    public function getSize();

    /**
     * Set cdn_status.
     *
     * @param int $cdnStatus
     */
    public function setCdnStatus($cdnStatus);

    /**
     * Get cdn_status.
     *
     * @return int $cdnStatus
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
}
