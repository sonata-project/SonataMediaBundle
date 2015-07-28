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
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath($relativePath, $isFlushable)
    {
        return sprintf('%s/%s', rtrim($this->path, '/'), ltrim($relativePath, '/'));
    }

    /**
     * {@inheritdoc}
     */
    public function flushByString($string)
    {
        // nothing to do
    }

    /**
     * {@inheritdoc}
     */
    public function flush($string)
    {
        // nothing to do
    }

    /**
     * {@inheritdoc}
     */
    public function flushPaths(array $paths)
    {
        // nothing to do
    }
}
