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

class Server implements CDNInterface
{
    protected $path;

    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($relativePath, $isFlushable = false)
    {
        return sprintf('%s/%s', $this->path, $relativePath);
    }

    /**
     * {@inheritDoc}
     */
    public function flushByString($string)
    {
        // nothing to do
    }

    /**
     * {@inheritDoc}
     */
    public function flush($string)
    {
        // nothing to do
    }

    /**
     * {@inheritDoc}
     */
    public function flushPaths(array $paths)
    {
        // nothing to do
    }
}