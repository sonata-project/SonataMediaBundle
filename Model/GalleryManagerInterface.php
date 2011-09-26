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
     * Creates an empty gallery instance
     *
     * @return Gallery
     */
    function create();

    /**
     * Deletes a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    function delete(GalleryInterface $gallery);

    /**
     * Finds one gallery by the given criteria
     *
     * @param array $criteria
     * @return GalleryInterface
     */
    function findOneBy(array $criteria);

    /**
     * Finds galleries by the given criteria
     *
     * @param array $criteria
     * @return array
     */
    function findBy(array $criteria);

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
    function update(GalleryInterface $gallery);
}