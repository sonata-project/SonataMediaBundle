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

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Server implements CDNInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = rtrim($path, '/');
    }

    public function getPath($relativePath, $isFlushable)
    {
        return sprintf('%s/%s', $this->path, ltrim($relativePath, '/'));
    }

    public function flushByString($string)
    {
        // nothing to do
    }

    public function flush($string)
    {
        // nothing to do
    }

    public function flushPaths(array $paths)
    {
        // nothing to do
    }

    public function getFlushStatus($identifier)
    {
        // nothing to do
    }
}
