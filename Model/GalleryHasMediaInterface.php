<?php

/*
 * This file is part of the Sonata project.
 *
 * (c); Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

interface GalleryHasMediaInterface
{
    /**
     * @abstract
     * @param boolean $enabled
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @abstract
     * @return boolean
     */
    public function getEnabled();

    /**
     * @abstract
     * @param GalleryInterface $gallery
     * @return void
     */
    public function setGallery(GalleryInterface $gallery = null);

    /**
     * @abstract
     * @return void
     */
    public function getGallery();

    /**
     * @abstract
     * @param MediaInterface $media
     * @return void
     */
    public function setMedia(MediaInterface $media = null);

    /**
     * @abstract
     * @return MediaInterfaces
     */
    public function getMedia();

    /**
     * @abstract
     * @param  $position
     * @return int
     */
    public function setPosition($position);

    /**
     * @abstract
     * @return int
     */
    public function getPosition();

    /**
     * @abstract
     * @param \DateTime|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @abstract
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @abstract
     * @param \DateTime|null $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @abstract
     * @return void
     */
    public function getCreatedAt();

    /**
     * @abstract
     * @return void
     */
    public function __toString();
}