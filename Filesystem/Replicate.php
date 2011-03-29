<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Filesystem;

use Gaufrette\Filesystem\Adapter as AdapterInterface;

class Replicate implements AdapterInterface
{

    protected $master;

    protected $slave;


    public function __construct(AdapterInterface $master, AdapterInterface $slave)
    {
        $this->master = $master;
        $this->slave  = $slave;
    }

    /**
     * Deletes the file
     *
     * @param  string $key
     *
     * @return boolean TRUE on success, or FALSE on failure
     */
    function delete($key)
    {
        if($this->slave->delete($key)) {
            return $this->master->delete($key);
        }

        return false;
    }

    /**
     * Returns the last modified time
     *
     * @param  string $key
     *
     * @return integer An UNIX like timestamp
     */
    function mtime($key)
    {
        return $this->master->mtime($key);
    }

    /**
     * Returns an array of all keys matching the specified pattern
     *
     * @param  string $pattern
     *
     * @return array
     */
    function keys($pattern)
    {
        return $this->master->keys($pattern);
    }

    /**
     * Indicates whether the file or directory exists
     *
     * @param  string $key
     *
     * @return boolean
     */
    function exists($key)
    {
        return $this->master->exists($key);
    }

    /**
     * Writes the given content into the file
     *
     * @param  string $key
     * @param  string $content
     *
     * @return integer The number of bytes that were written into the file, or
     *                 FALSE on failure
     */
    function write($key, $content)
    {
        if($this->master->write($key, $content)) {
            return $this->slave->write($key, $content);
        }

        return false;
    }

    /**
     * Reads the content of the file
     *
     * @param  string $key
     *
     * @return string
     */
    function read($key)
    {
        return $this->master->delete($key);
    }
}