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

use Imagine\Image\Box;
use Symfony\Component\Validator\ExecutionContextInterface;

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
    protected $context;

    /**
     * @var boolean $cdn_is_flushable
     */
    protected $cdnIsFlushable;

    /**
     * @var datetime $cdn_flush_at
     */
    protected $cdnFlushAt;

    /**
     * @var integer $cdn_status
     */
    protected $cdnStatus;

    /**
     * @var datetime $updated_at
     */
    protected $updatedAt;

    /**
     * @var datetime $created_at
     */
    protected $createdAt;

    protected $binaryContent;

    protected $previousProviderReference;

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

    /**
     * @static
     * @return array
     */
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

    /**
     * {@inheritdoc}
     */
    public function setBinaryContent($binaryContent)
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    /**
     * {@inheritdoc}
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataValue($name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return isset($metadata[$name]) ? $metadata[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataValue($name, $value)
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetMetadataValue($name)
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderStatus($providerStatus)
    {
        $this->providerStatus = $providerStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderStatus()
    {
        return $this->providerStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderMetadata(array $providerMetadata = array())
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return $this->providerMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnIsFlushable($cdnIsFlushable)
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnIsFlushable()
    {
        return $this->cdnIsFlushable;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnFlushAt(\DateTime $cdnFlushAt = null)
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnFlushAt()
    {
        return $this->cdnFlushAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        return pathinfo($this->getProviderReference(), PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnStatus($cdnStatus)
    {
        $this->cdnStatus = $cdnStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnStatus()
    {
        return $this->cdnStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getBox()
    {
        return new Box($this->width, $this->height);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    /**
     * {@inheritdoc}
     */
    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = $galleryHasMedias;
    }

    /**
     * {@inheritdoc}
     */
    public function getGalleryHasMedias()
    {
        return $this->galleryHasMedias;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousProviderReference()
    {
        return $this->previousProviderReference;
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isStatusErroneous(ExecutionContextInterface $context)
    {
        if ($this->getBinaryContent() && $this->getProviderStatus() == self::STATUS_ERROR) {
            $context->addViolationAt('binaryContent', 'invalid', array(), null);
        }
    }
}
