<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

abstract class Media implements MediaInterface
{
    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var text $description
     */
    protected $description;

    /**
     * @var boolean $enabled
     */
    protected $enabled = false;

    /**
     * @var string $provider_name
     */
    protected $providerName;

    /**
     * @var integer $provider_status
     */
    protected $providerStatus;

    /**
     * @var string $provider_reference
     */
    protected $providerReference;

    /**
     * @var array $provider_metadata
     */
    protected $providerMetadata = array();

    /**
     * @var integer $width
     */
    protected $width;

    /**
     * @var integer $height
     */
    protected $height;

    /**
     * @var decimal $length
     */
    protected $length;

    /**
     * @var string $copyright
     */
    protected $copyright;

    /**
     * @var string $author_name
     */
    protected $authorName;

    /**
     * @var string $context
     */
    protected $context = 'default';

    /**
     * @var boolean $cdn_is_flushable
     */
    protected $cdnIsFlushable;

    /**
     * @var datetime $cdn_flush_at
     */
    protected $cdnFlushAt;

    /**
     * @var datetime $updated_at
     */
    protected $updatedAt;

    /**
     * @var datetime $created_at
     */
    protected $createdAt;


    protected $binaryContent;

    /**
     * @var varchar $content_type
     */
    protected $contentType;

    /**
     * @var integer $size
     */
    protected $size;

    protected $galleryHasMedias;


    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime);
        $this->setUpdatedAt(new \DateTime);
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime);
    }

    public static function getStatusList()
    {
        return array(
            self::STATUS_OK          => 'ok',
            self::STATUS_SENDING     => 'sending',
            self::STATUS_PENDING     => 'pending',
            self::STATUS_ERROR       => 'error',
            self::STATUS_ENCODING    => 'encoding',
        );
    }

    public function setBinaryContent($binaryContent)
    {
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    public function getMetadataValue($name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return isset($metadata[$name]) ? $metadata[$name] : $default;
    }

    /**
     * Add a named data value to the metadata
     *
     * @param string $name
     * @param string$value
     */
    public function setMetadataValue($name, $value)
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * Remove a named data from the metadata
     *
     * @param string $name
     */
    public function unsetMetadataValue($name)
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set provider_name
     *
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * Get provider_name
     *
     * @return string $providerName
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * Set provider_status
     *
     * @param integer $providerStatus
     */
    public function setProviderStatus($providerStatus)
    {
        $this->providerStatus = $providerStatus;
    }

    /**
     * Get provider_status
     *
     * @return integer $providerStatus
     */
    public function getProviderStatus()
    {
        return $this->providerStatus;
    }

    /**
     * Set provider_reference
     *
     * @param string $providerReference
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;
    }

    /**
     * Get provider_reference
     *
     * @return string $providerReference
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * Set provider_metadata
     *
     * @param array $providerMetadata
     */
    public function setProviderMetadata(array $providerMetadata = array())
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * Get provider_metadata
     *
     * @return array $providerMetadata
     */
    public function getProviderMetadata()
    {
        return $this->providerMetadata;
    }

    /**
     * Set width
     *
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get width
     *
     * @return integer $width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return integer $height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set length
     *
     * @param decimal $length
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * Get length
     *
     * @return decimal $length
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set copyright
     *
     * @param string $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * Get copyright
     *
     * @return string $copyright
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Set author_name
     *
     * @param string $authorName
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * Get author_name
     *
     * @return string $authorName
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Set context
     *
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * Get context
     *
     * @return string $context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set cdn_is_flushable
     *
     * @param boolean $cdnIsFlushable
     */
    public function setCdnIsFlushable($cdnIsFlushable)
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    /**
     * Get cdn_is_flushable
     *
     * @return boolean $cdnIsFlushable
     */
    public function getCdnIsFlushable()
    {
        return $this->cdnIsFlushable;
    }

    /**
     * Set cdn_flush_at
     *
     * @param datetime $cdnFlushAt
     */
    public function setCdnFlushAt(\DateTime $cdnFlushAt = null)
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    /**
     * Get cdn_flush_at
     *
     * @return datetime $cdnFlushAt
     */
    public function getCdnFlushAt()
    {
        return $this->cdnFlushAt;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set content_type
     *
     * @param varchar $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Get content_type
     *
     * @return varchar $contentType
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return pathinfo($this->getProviderReference(), PATHINFO_EXTENSION);
    }

    /**
     * Set size
     *
     * @param integer $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Get size
     *
     * @return integer $size
     */
    public function getSize()
    {
        return $this->size;
    }

    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = $galleryHasMedias;
    }

    public function getGalleryHasMedias()
    {
        return $this->galleryHasMedias;
    }
}