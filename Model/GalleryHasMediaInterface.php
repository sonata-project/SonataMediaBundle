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
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param GalleryInterface $gallery
     */
    public function setGallery(GalleryInterface $gallery = null);

    /**
     */
    public function getGallery();

    /**
     * @param MediaInterface $media
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
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     */
    public function getCreatedAt();

    /**
     */
    public function __toString();
}
