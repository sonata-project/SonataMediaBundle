<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\CDN;

interface CDNInterface
{
    /**
     * return the base path 
     *
     * @param string $relativePath
     * @param boolean $isFlushable
     * @return string
     */
    function getPath($relativePath, $isFlushable);

    /**
     * Flush the ressource
     *
     * @abstract
     * @param string $media
     * @return void
     */
    function flush($string);

    /**
     * Flush a set of ressource matching the provided string
     *
     * @param string $string
     * @return void
     */
    function flushByString($string);

    /**
     * Flush a different set of ressource matching the provided string array
     *
     * @param string $string
     * @return void
     */
    function flushPaths(array $paths);
}