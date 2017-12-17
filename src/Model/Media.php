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
use Symfony\Component\Validator\Context\ExecutionContextInterface;

abstract class Media implements MediaInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var int
     */
    protected $providerStatus;

    /**
     * @var string
     */
    protected $providerReference;

    /**
     * @var array
     */
    protected $providerMetadata = [];

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var float
     */
    protected $length;

    /**
     * @var string
     */
    protected $copyright;

    /**
     * @var string
     */
    protected $authorName;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var bool
     */
    protected $cdnIsFlushable;

    /**
     * @var string
     */
    protected $cdnFlushIdentifier;

    /**
     * @var \DateTime
     */
    protected $cdnFlushAt;

    /**
     * @var int
     */
    protected $cdnStatus;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var mixed
     */
    protected $binaryContent;

    /**
     * @var string
     */
    protected $previousProviderReference;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var GalleryItemInterface[]
     */
    protected $galleryItems;

    /**
     * @var CategoryInterface
     */
    protected $category;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    public function prePersist(): void
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(): void
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @static
     *
     * @return string[]
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_OK => 'ok',
            self::STATUS_SENDING => 'sending',
            self::STATUS_PENDING => 'pending',
            self::STATUS_ERROR => 'error',
            self::STATUS_ENCODING => 'encoding',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setBinaryContent($binaryContent): void
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    /**
     * {@inheritdoc}
     */
    public function resetBinaryContent(): void
    {
        $this->binaryContent = null;
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

        return $metadata[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataValue($name, $value): void
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetMetadataValue($name): void
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name): void
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
    public function setDescription($description): void
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
    public function setEnabled($enabled): void
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
    public function setProviderName($providerName): void
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
    public function setProviderStatus($providerStatus): void
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
    public function setProviderReference($providerReference): void
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
    public function setProviderMetadata(array $providerMetadata = []): void
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
    public function setWidth($width): void
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
    public function setHeight($height): void
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
    public function setLength($length): void
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
    public function setCopyright($copyright): void
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
    public function setAuthorName($authorName): void
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
    public function setContext($context): void
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
    public function setCdnIsFlushable($cdnIsFlushable): void
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
    public function setCdnFlushIdentifier($cdnFlushIdentifier): void
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnFlushIdentifier()
    {
        return $this->cdnFlushIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnFlushAt(\DateTime $cdnFlushAt = null): void
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
    public function setUpdatedAt(\DateTime $updatedAt = null): void
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
    public function setCreatedAt(\DateTime $createdAt = null): void
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
    public function setContentType($contentType): void
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
        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($this->getProviderReference(), PATHINFO_EXTENSION));
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size): void
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
    public function setCdnStatus($cdnStatus): void
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
    public function setGalleryItems($galleryItems): void
    {
        $this->galleryItems = $galleryItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getGalleryItems()
    {
        return $this->galleryItems;
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
    public function isStatusErroneous(ExecutionContextInterface $context): void
    {
        if ($this->getBinaryContent() && self::STATUS_ERROR == $this->getProviderStatus()) {
            $context->buildViolation('invalid')->atPath('binaryContent')->addViolation();
        }
    }

    /**
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param CategoryInterface|null $category
     */
    public function setCategory($category = null): void
    {
        if (null !== $category && !is_a($category, CategoryInterface::class)) {
            throw new \InvalidArgumentException(
                '$category should be an instance of Sonata\ClassificationBundle\Model\CategoryInterface or null'
            );
        }

        $this->category = $category;
    }
}
