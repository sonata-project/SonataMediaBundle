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

use Gaufrette\Adapter as AdapterInterface;
use Gaufrette\Filesystem;

class Replicate implements AdapterInterface
{
    protected $master;

    protected $slave;

    /**
     * @param \Gaufrette\Adapter $master
     * @param \Gaufrette\Adapter $slave
     */
    public function __construct(AdapterInterface $master, AdapterInterface $slave)
    {
        $this->master = $master;
        $this->slave  = $slave;
    }

    /**
     * Returns the checksum of the file
     *
     * @param string $key
     *
     * @return string
     */
    public function checksum($key)
    {
        return $this->master->checksum($key);
    }

    /**
     * Deletes the file
     *
     * @param string $key
     *
     * @return void TRUE on success, or FALSE on failure
     */
    public function delete($key)
    {
        $this->slave->delete($key);
        $this->master->delete($key);
    }

    /**
     * Returns the last modified time
     *
     * @param string $key
     *
     * @return integer An UNIX like timestamp
     */
    public function mtime($key)
    {
        return $this->master->mtime($key);
    }

    /**
     * Returns an array of all keys matching the specified pattern
     *
     * @return array
     */
    public function keys()
    {
        return $this->master->keys();
    }

    /**
     * Indicates whether the file or directory exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function exists($key)
    {
        return $this->master->exists($key);
    }

    /**
     * Writes the given content into the file
     *
     * @param string $key
     * @param string $content
     *
     * @return integer The number of bytes that were written into the file, or
     *                 FALSE on failure
     */
    public function write($key, $content, array $metadata = null)
    {
        $return = $this->master->write($key, $content, $metadata);
        $this->slave->write($key, $content, $metadata);

        return $return;
    }

    /**
     * Reads the content of the file
     *
     * @param string $key
     *
     * @return string
     */
    public function read($key)
    {
        return $this->master->read($key);
    }

    /**
     * Renames a file
     *
     * @param string $key
     * @param string $new
     *
     * @throws RuntimeException on failure
     */
    public function rename($key, $new)
    {
        $this->master->rename($key, $new);
        $this->slave->rename($key, $new);
    }

    /**
     * If the adapter can allow inserting metadata
     *
     * @return bool true if supports metadata, false if not
     */
    public function supportsMetadata()
    {
        return $this->master->supportsMetadata() && $this->slave->supportsMetadata();
    }

    /**
     * {@inheritDoc}
     */
    public function createFile($key, Filesystem $filesystem)
    {
        return $this->master->createFile($key, $filesystem);
    }

    /**
     * {@inheritDoc}
     */
    public function createFileStream($key, Filesystem $filesystem)
    {
        return $this->master->createFileStream($key, $filesystem);
    }

    /**
     * {@inheritDoc}
     */
    public function listDirectory($directory = '')
    {
        return $this->master->listDirectory($directory);
    }
}
