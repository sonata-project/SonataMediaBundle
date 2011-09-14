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
     * Creates an empty media instance
     *
     * @return Media
     */
    function create();

    /**
     * Deletes a media
     *
     * @param Media $media
     * @return void
     */
    function delete(MediaInterface $media);

    /**
     * Finds many media by the given criteria
     *
     * @param array $criteria
     * @return MediaInterface
     */
    function findBy(array $criteria);

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     * @return MediaInterface
     */
    function findOneBy(array $criteria);

    /**
     * Returns the media's fully qualified class name
     *
     * @return string
     */
    function getClass();

    /**
     * @abstract
     * @param MediaInterface $media
     * @param null $context
     * @param null $providerName
     * @return void
     */
    function save(MediaInterface $media, $context = null, $providerName = null);
}