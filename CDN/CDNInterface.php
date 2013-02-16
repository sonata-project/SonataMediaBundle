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
    const STATUS_OK       = 1;
    const STATUS_TO_SEND  = 2;
    const STATUS_TO_FLUSH = 3;
    const STATUS_ERROR    = 4;
    const STATUS_WAITING  = 5;

    /**
     * Return the base path
     *
     * @param string  $relativePath
     * @param boolean $isFlushable
     *
     * @return string
     */
    public function getPath($relativePath, $isFlushable);

    /**
     * Flush the resource
     *
     * @param string $string
     *
     * @return void
     */
    public function flush($string);

    /**
     * Flush a set of resources matching the provided string
     *
     * @param string $string
     *
     * @return void
     */
    public function flushByString($string);

    /**
     * Flush a set of resources matching the paths in provided array
     *
     * @param array $paths
     *
     * @return void
     */
    public function flushPaths(array $paths);
}
