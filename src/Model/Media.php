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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

abstract class Media implements MediaInterface
{
    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string|null
     */
    protected $providerName;

    /**
     * @var int|null
     */
    protected $providerStatus;

    /**
     * @var string|null
     */
    protected $providerReference;

    /**
     * @var array
     */
    protected $providerMetadata = [];

    /**
     * @var int|null
     */
    protected $width;

    /**
     * @var int|null
     */
    protected $height;

    /**
     * @var float|null
     */
    protected $length;

    /**
     * @var string|null
     */
    protected $copyright;

    /**
     * @var string|null
     */
    protected $authorName;

    /**
     * @var string|null
     */
    protected $context;

    /**
     * @var bool
     */
    protected $cdnIsFlushable = false;

    /**
     * @var string|null
     */
    protected $cdnFlushIdentifier;

    /**
     * @var \DateTime|null
     */
    protected $cdnFlushAt;

    /**
     * @var int|null
     */
    protected $cdnStatus;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var mixed
     */
    protected $binaryContent;

    /**
     * @var string|null
     */
    protected $previousProviderReference;

    /**
     * @var string|null
     */
    protected $contentType;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * @var GalleryHasMediaInterface[]
     */
    protected $galleryHasMedias;

    /**
     * @var CategoryInterface|null
     */
    protected $category;

    public function __toString()
    {
        return $this->getName() ?? 'n/a';
    }

    // NEXT_MAJOR: Remove this method
    public function __set($property, $value)
    {
        if ('category' === $property) {
            if (null !== $value && !is_a($value, CategoryInterface::class)) {
                throw new \InvalidArgumentException(
                    '$category should be an instance of Sonata\ClassificationBundle\Model\CategoryInterface or null'
                );
            }

            $this->category = $value;
        }
    }

    // NEXT_MAJOR: Remove this method
    public function __call($method, $arguments)
    {
        if ('setCategory' === $method) {
            $this->__set('category', current($arguments));
        }
    }

    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate()
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
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setBinaryContent($binaryContent)
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function resetBinaryContent()
    {
        $this->binaryContent = null;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getMetadataValue($name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return $metadata[$name] ?? $default;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setMetadataValue($name, $value)
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function unsetMetadataValue($name)
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setProviderStatus($providerStatus)
    {
        $this->providerStatus = $providerStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getProviderStatus()
    {
        return $this->providerStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setProviderMetadata(array $providerMetadata = [])
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getProviderMetadata()
    {
        return $this->providerMetadata;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCdnIsFlushable($cdnIsFlushable)
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCdnIsFlushable()
    {
        return $this->cdnIsFlushable;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCdnFlushIdentifier($cdnFlushIdentifier)
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCdnFlushIdentifier()
    {
        return $this->cdnFlushIdentifier;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCdnFlushAt(?\DateTime $cdnFlushAt = null)
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCdnFlushAt()
    {
        return $this->cdnFlushAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getExtension()
    {
        $providerReference = $this->getProviderReference();
        if (null === $providerReference) {
            return null;
        }

        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($providerReference, \PATHINFO_EXTENSION));
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function setCdnStatus($cdnStatus)
    {
        $this->cdnStatus = $cdnStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getCdnStatus()
    {
        return $this->cdnStatus;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getBox()
    {
        return new Box($this->width, $this->height);
    }

    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = $galleryHasMedias;
    }

    public function getGalleryHasMedias()
    {
        return $this->galleryHasMedias;
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function getPreviousProviderReference()
    {
        return $this->previousProviderReference;
    }

    /**
     * NEXT_MAJOR: Remove this method when bumping Symfony requirement to 2.8+.
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new Assert\Callback('isStatusErroneous'));
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function isStatusErroneous($context)
    {
        if (null !== $this->getBinaryContent() && self::STATUS_ERROR === $this->getProviderStatus()) {
            // NEXT_MAJOR: Restore type hint
            if (!$context instanceof ExecutionContextInterface) {
                throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Validator\ExecutionContextInterface');
            }

            $context->buildViolation('invalid')->atPath('binaryContent')->addViolation();
        }
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     *
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }

    // NEXT_MAJOR: Uncomment this method and remove __call and __set
    // final public function setCategory(?object $category = null): void
    // {
    //     $this->category = $category;
    // }
}
