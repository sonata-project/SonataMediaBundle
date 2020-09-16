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

    public function __toString()
    {
        return $this->getName() ?: '-';
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setDefaultFormat($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = new ArrayCollection();

        foreach ($galleryHasMedias as $galleryHasMedia) {
            $this->addGalleryHasMedia($galleryHasMedia);
        }
    }

    public function getGalleryHasMedias()
    {
        return $this->galleryHasMedias;
    }

    public function addGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia)
    {
        $galleryHasMedia->setGallery($this);

        $this->galleryHasMedias[] = $galleryHasMedia;
    }

    public function removeGalleryHasMedia(GalleryHasMediaInterface $galleryHasMedia)
    {
        $this->galleryHasMedias->removeElement($galleryHasMedia);
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated use addGalleryHasMedia method instead
     * NEXT_MAJOR: remove this method with the next major release
     */
    public function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia)
    {
        @trigger_error(
            'The '.__METHOD__.' is deprecated and will be removed with next major release.'
            .'Use `addGalleryHasMedia` method instead.',
            E_USER_DEPRECATED
        );
        $this->addGalleryHasMedia($galleryHasMedia);
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Reorders $galleryHasMedia items based on their position.
     */
    public function reorderGalleryHasMedia()
    {
        $galleryHasMedias = $this->getGalleryHasMedias();

        if ($galleryHasMedias instanceof \IteratorAggregate) {
            // reorder
            $iterator = $galleryHasMedias->getIterator();

            $iterator->uasort(static function (GalleryHasMediaInterface $a, GalleryHasMediaInterface $b): int {
                return $a->getPosition() <=> $b->getPosition();
            });

            $this->setGalleryHasMedias($iterator);
        }
    }
}
