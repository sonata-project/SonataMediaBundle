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
     * @param $relativePath
     * @param bool $isFlushable
     * @return string
     */
    public function getPath($relativePath, $isFlushable = false)
    {
        return sprintf('%s/%s', $this->path, $relativePath);
    }

    /**
     * Flush a set of ressource matching the provided string
     *
     * @param string $string
     * @return void
     */
    function flushByString($string)
    {
        // nothing to do
    }

    /**
     * Flush the ressource
     *
     * @param string $string
     * @return void
     */
    function flush($string)
    {
        // nothing to do
    }

    /**
    * Flush a different set of ressource matching the provided string array
    *
    * @param array $paths
    * @return void
    */
    function flushPaths(array $paths)
    {
        // nothing to do
    }
}