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


interface MediaManagerInterface
{

    /**
     * Creates an empty medie instance
     *
     * @return Media
     */
    function createMedia();

    /**
     * Deletes a media
     *
     * @param Media $user
     * @return void
     */
    function deleteMedia(MediaInterface $media);

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     * @return MediaInterface
     */
    function findMediaBy(array $criteria);

    /**
     * Returns the user's fully qualified class name
     *
     * @return string
     */
    function getClass();

    /**
     * Updates a media
     *
     * @param Media $media
     * @return void
     */
    function updateUser(MediaInterface $media);
}