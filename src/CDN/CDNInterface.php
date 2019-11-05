<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\CDN;

interface CDNInterface
{
    public const STATUS_OK = 1;
    public const STATUS_TO_SEND = 2;
    public const STATUS_TO_FLUSH = 3;
    public const STATUS_ERROR = 4;
    public const STATUS_WAITING = 5;

    /**
     * Return the base path.
     *
     * @param string $relativePath
     * @param bool   $isFlushable
     *
     * @return string
     */
    public function getPath($relativePath, $isFlushable);

    /**
     * Flush the resource.
     *
     * @param string $string
     *
     * @return void|string
     */
    public function flush($string);

    /**
     * Flush a set of resources matching the provided string.
     *
     * @param string $string
     *
     * @return void|string
     */
    public function flushByString($string);

    /**
     * Flush a set of resources matching the paths in provided array.
     *
     * @return void|string
     */
    public function flushPaths(array $paths);

    /**
     * Return the CDN status for given identifier.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function getFlushStatus($identifier);
}
