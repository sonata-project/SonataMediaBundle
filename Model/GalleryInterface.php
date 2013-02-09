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

interface GalleryInterface
{
    /**
     * Set name
     *
     * @param string $name
     */
    function setName($name);

    /**
     * @return string
     */
    function getContext();

    /**
     * @param string $context
     *
     * @return string
     */
    function setContext($context);

    /**
     * Get name
     *
     * @return string $name
     */
    function getName();

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    function getEnabled();

    /**
     * Set updated_at
     *
     * @param \Datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updated_at
     *
     * @return \Datetime $updatedAt
     */
    function getUpdatedAt();

    /**
     * Set created_at
     *
     * @param \Datetime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get created_at
     *
     * @return \Datetime $createdAt
     */
    function getCreatedAt();

    /**
     * @param string $defaultFormat
     */
    function setDefaultFormat($defaultFormat);

    /**
     * @return string
     */
    function getDefaultFormat();

    /**
     * @param array $galleryHasMedias
     */
    function setGalleryHasMedias($galleryHasMedias);

    /**
     * @return array
     */
    function getGalleryHasMedias();

    /**
     * @param GalleryHasMediaInterface $galleryHasMedia
     */
    function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia);

    /**
     * @return string
     */
    function __toString();
}