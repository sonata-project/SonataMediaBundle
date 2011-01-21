<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Entity;

class BaseMedia {

    const  STATUS_OK          = 1;
    const  STATUS_SENDING     = 2;
    const  STATUS_PENDING     = 3;
    const  STATUS_ERROR       = 4;
    const  STATUS_ENCODING    = 4;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    
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
    protected $provider_name;

    /**
     * @var integer $provider_status
     */
    protected $provider_status;

    /**
     * @var string $provider_reference
     */
    protected $provider_reference;

    /**
     * @var array $provider_metadata
     */
    protected $provider_metadata = array();

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
    protected $author_name;

    /**
     * @var string $context
     */
    protected $context;

    /**
     * @var boolean $cdn_is_flushable
     */
    protected $cdn_is_flushable;

    /**
     * @var datetime $cdn_flush_at
     */
    protected $cdn_flush_at;

    /**
     * @var datetime $updated_at
     */
    protected $updated_at;

    /**
     * @var datetime $created_at
     */
    protected $created_at;


    protected $binary_content;


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
    
    public function setBinaryContent($binary_content)
    {

        $this->provider_reference = null;
        $this->binary_content = $binary_content;
    }

    public function getBinaryContent()
    {

        return $this->binary_content;
    }

    public function getMetadataValue($name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        if(!is_array($metadata)) {
            $metadata = array();
        }

        return isset($metadata[$name]) ? $metadata[$name] : $default;
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
        $this->provider_name = $providerName;
    }

    /**
     * Get provider_name
     *
     * @return string $providerName
     */
    public function getProviderName()
    {
        return $this->provider_name;
    }

    /**
     * Set provider_status
     *
     * @param integer $providerStatus
     */
    public function setProviderStatus($providerStatus)
    {
        $this->provider_status = $providerStatus;
    }

    /**
     * Get provider_status
     *
     * @return integer $providerStatus
     */
    public function getProviderStatus()
    {
        return $this->provider_status;
    }

    /**
     * Set provider_reference
     *
     * @param string $providerReference
     */
    public function setProviderReference($providerReference)
    {
        $this->provider_reference = $providerReference;
    }

    /**
     * Get provider_reference
     *
     * @return string $providerReference
     */
    public function getProviderReference()
    {
        return $this->provider_reference;
    }

    /**
     * Set provider_metadata
     *
     * @param array $providerMetadata
     */
    public function setProviderMetadata($providerMetadata)
    {
        $this->provider_metadata = $providerMetadata;
    }

    /**
     * Get provider_metadata
     *
     * @return array $providerMetadata
     */
    public function getProviderMetadata()
    {
        return $this->provider_metadata;
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
        $this->author_name = $authorName;
    }

    /**
     * Get author_name
     *
     * @return string $authorName
     */
    public function getAuthorName()
    {
        return $this->author_name;
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
        $this->cdn_is_flushable = $cdnIsFlushable;
    }

    /**
     * Get cdn_is_flushable
     *
     * @return boolean $cdnIsFlushable
     */
    public function getCdnIsFlushable()
    {
        return $this->cdn_is_flushable;
    }

    /**
     * Set cdn_flush_at
     *
     * @param datetime $cdnFlushAt
     */
    public function setCdnFlushAt($cdnFlushAt)
    {
        $this->cdn_flush_at = $cdnFlushAt;
    }

    /**
     * Get cdn_flush_at
     *
     * @return datetime $cdnFlushAt
     */
    public function getCdnFlushAt()
    {
        return $this->cdn_flush_at;
    }

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    }

    /**
     * Get updated_at
     *
     * @return datetime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set created_at
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    }

    /**
     * Get created_at
     *
     * @return datetime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
    /**
     * @var varchar $content_type
     */
    protected $content_type;

    /**
     * @var integer $size
     */
    protected $size;

    /**
     * Set content_type
     *
     * @param varchar $contentType
     */
    public function setContentType($contentType)
    {
        $this->content_type = $contentType;
    }

    /**
     * Get content_type
     *
     * @return varchar $contentType
     */
    public function getContentType()
    {
        return $this->content_type;
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
}