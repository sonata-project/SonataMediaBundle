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

interface GalleryHasMediaInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return bool
     */
    public function getEnabled();

    /**
     * @param GalleryInterface|null $gallery
     */
    public function setGallery(GalleryInterface $gallery = null);

    /**
     * @return GalleryInterface|null
     */
    public function getGallery();

    /**
     * @param MediaInterface|null $media
     */
    public function setMedia(MediaInterface $media = null);

    /**
     * @return MediaInterface|null
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
     * @return \DateTime|null
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime|null $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt();
}
