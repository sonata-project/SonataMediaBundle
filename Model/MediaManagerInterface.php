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
     * @return MediaInterface
     */
    public function create();

    /**
     * Deletes a media
     *
     * @param MediaInterface $media
     *
     * @return void
     */
    public function delete(MediaInterface $media);

    /**
     * Finds many media by the given criteria
     *
     * @param array $criteria
     *
     * @return MediaInterface
     */
    public function findBy(array $criteria);

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     *
     * @return MediaInterface
     */
    public function findOneBy(array $criteria);

    /**
     * Returns the media's fully qualified class name
     *
     * @return string
     */
    public function getClass();

    /**
     * @param MediaInterface $media
     * @param null           $context
     * @param null           $providerName
     *
     * @return void
     */
    public function save(MediaInterface $media, $context = null, $providerName = null);
}
