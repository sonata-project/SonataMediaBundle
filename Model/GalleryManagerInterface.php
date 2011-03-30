<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;


interface GalleryManagerInterface
{

    /**
     * Creates an empty medie instance
     *
     * @return Gallery
     */
    function createGallery();

    /**
     * Deletes a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    function deleteGallery(GalleryInterface $gallery);

    /**
     * Finds one gallery by the given criteria
     *
     * @param array $criteria
     * @return GalleryInterface
     */
    function findGalleryBy(array $criteria);

    /**
     * Finds galleries by the given criteria
     *
     * @param array $criteria
     * @return array
     */
    function findGalleriesBy(array $criteria);

    /**
     * Returns the gallery's fully qualified class name
     *
     * @return string
     */
    function getClass();

    /**
     * Updates a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    function updateGallery(GalleryInterface $gallery);


}