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
     * @param boolean $enabled
     *
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * @return boolean
     */
    public function getEnabled();

    /**
     * @param GalleryInterface $gallery
     *
     * @return void
     */
    public function setGallery(GalleryInterface $gallery = null);

    /**
     * @return void
     */
    public function getGallery();

    /**
     * @param MediaInterface $media
     *
     * @return void
     */
    public function setMedia(MediaInterface $media = null);

    /**
     * @return MediaInterface
     */
    public function getMedia();

    /**
     * @param int $position
     *
     * @return int
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime|null $createdAt
     *
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return void
     */
    public function getCreatedAt();

    /**
     * @return void
     */
    public function __toString();
}
