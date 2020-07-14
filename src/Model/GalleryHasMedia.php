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

abstract class GalleryHasMedia implements GalleryHasMediaInterface
{
    /**
     * @var MediaInterface|null
     */
    protected $media;

    /**
     * @var GalleryInterface|null
     */
    protected $gallery;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var \DateTime|null
     */
    protected $updatedAt;

    /**
     * @var \DateTime|null
     */
    protected $createdAt;

    /**
     * @var bool
     */
    protected $enabled = false;

    public function __toString()
    {
        return $this->getGallery().' | '.$this->getMedia();
    }

    public function setCreatedAt(?\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setGallery(?GalleryInterface $gallery = null)
    {
        $this->gallery = $gallery;
    }

    public function getGallery()
    {
        return $this->gallery;
    }

    public function setMedia(?MediaInterface $media = null)
    {
        $this->media = $media;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
