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
use Sonata\MediaBundle\Provider\MediaProviderInterface;

/**
 * NEXT_MAJOR: remove GalleryMediaCollectionInterface interface. Move its content into GalleryInterface.
 */
abstract class Gallery implements GalleryInterface, GalleryMediaCollectionInterface
{
    /**
     * @var string
     */
    protected $context;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $defaultFormat = MediaProviderInterface::FORMAT_REFERENCE;

    /**
     * @var GalleryHasMediaInterface[]|Collection
     */
    protected $galleryHasMedias;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: '-';
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
    public function setDefaultFormat($defaultFormat): void
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function setGalleryHasMedias($galleryHasMedias): void
    {
        $this->galleryHasMedias = new ArrayCollection();

        foreach ($galleryHasMedias as $galleryHasMedia) {
            $this->addGalleryHasMedia($galleryHasMedia);
        }
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
    public function addGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia): void
    {
        $galleryHasMedia->setGallery($this);

        $this->galleryHasMedias[] = $galleryHasMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function removeGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia): void
    {
        $this->galleryHasMedias->removeElement($galleryHasMedia);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated use addGalleryHasMedia method instead
     * NEXT_MAJOR: remove this method with the next major release
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia): void
    {
        @trigger_error(
            'The '.__METHOD__.' is deprecated and will be removed with next major release.'
            .'Use `addGalleryHasMedia` method instead.',
            E_USER_DEPRECATED
        );
        $this->addGalleryHasMedia($galleryHasMedia);
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
}
