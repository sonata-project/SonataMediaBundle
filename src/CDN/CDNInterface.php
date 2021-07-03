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
     */
    public function getPath(string $relativePath, bool $isFlushable = false): string;

    /**
     * Flush the resource.
     */
    public function flush(string $string): string;

    /**
     * Flush a set of resources matching the provided string.
     */
    public function flushByString(string $string): string;

    /**
     * Flush a set of resources matching the paths in provided array.
     *
     * @param string[] $paths
     */
    public function flushPaths(array $paths): string;

    /**
     * Return the CDN status for given identifier.
     */
    public function getFlushStatus(string $identifier): int;
}
