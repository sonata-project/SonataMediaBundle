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
     * @abstract
     * @param string $name
     */
    function setName($name);

    /**
     * Get name
     *
     * @abstract
     * @return string $name
     */
    function getName();

    /**
     * Set enabled
     *
     * @abstract
     * @param boolean $enabled
     */
    function setEnabled($enabled);

    /**
     * Get enabled
     *
     * @abstract
     * @return boolean $enabled
     */
    function getEnabled();

    /**
     * Set updated_at
     *
     * @param datetime $updatedAt
     */
    function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Get updated_at
     *
     * @abstract
     * @return datetime $updatedAt
     */
    function getUpdatedAt();

    /**
     * Set created_at
     *
     * @abstract
     * @param datetime $createdAt
     */
    function setCreatedAt(\DateTime $createdAt = null);

    /**
     * Get created_at
     *
     * @abstract
     * @return datetime $createdAt
     */
    function getCreatedAt();

    /**
     * @abstract
     * @param string $defaultFormat
     * @return void
     */
    function setDefaultFormat($defaultFormat);

    /**
     * @abstract
     * @return void
     */
    function getDefaultFormat();

    /**
     * @abstract
     * @param string $code
     * @return void
     */
    function setCode($code);

    /**
     * @abstract
     * @return void
     */
    function getCode();

    /**
     * @abstract
     * @param  $galleryHasMedias
     * @return void
     */
    function setGalleryHasMedias($galleryHasMedias);

    /**
     * @abstract
     * @return void
     */
    function getGalleryHasMedias();

    /**
     * @abstract
     * @param BaseGalleryHasMedia $galleryHasMedia
     * @return void
     */
    function addGalleryHasMedias(GalleryHasMediaInterface $galleryHasMedia);

    /**
     * @abstract
     * @return string
     */
    function __toString();
}