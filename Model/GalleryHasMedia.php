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

abstract class GalleryHasMedia implements GalleryHasMediaInterface
{
    /**
     * @var MediaInterface
     */
    protected $media;

    /**
     * @var GalleryInterface
     */
    protected $gallery;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getGallery().' | '.$this->getMedia();
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
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
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
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
    public function setGallery(GalleryInterface $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * {@inheritdoc}
     */
    public function setMedia(MediaInterface $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * {@inheritdoc}
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
