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
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Filesystem;

class Replicate implements AdapterInterface, MetadataSupporter
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
     * Deletes the file
     *
     * @param string $key
     *
     * @return void TRUE on success, or FALSE on failure
     */
    public function delete($key)
    {
        return $this->slave->delete($key) && $this->master->delete($key);
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
     * If one of the adapters can allow inserting metadata
     *
     * @return bool true if supports metadata, false if not
     */
    public function supportsMetadata()
    {
        return $this->master instanceof MetadataSupporter ||  $this->slave instanceof MetadataSupporter;
    }

    /**
     * Sets metadata for adapters if they allow it
     *
     * @param string $key
     * @param array  $metadata
     *
     */
    public function setMetadata($key, $metadata)
    {
        if ($this->master instanceof MetadataSupporter) {
            $this->master->setMetadata($key, $metadata);
        }
        if ($this->slave instanceof MetadataSupporter) {
            $this->slave->setMetadata($key, $metadata);
        }
    }

    /**
     * Gets metadata for master or slave adapter if they allow it
     *
     * @param string $key
     *
     */
    public function getMetadata($key)
    {
        if ($this->master instanceof MetadataSupporter) {
            return $this->master->getMetadata($key);
        } elseif ($this->slave instanceof MetadataSupporter) {
            return $this->slave->getMetadata($key);
        }

        return array();
    }

    /**
     * Gets the class names as an array for both adapters
     *
     * @return array
     *
     */
    public function getAdapterClassNames()
    {
        return array(
            get_class($this->master),
            get_class($this->slave),
        );
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

    /**
     * {@inheritDoc}
     */
    public function isDirectory($key)
    {
        return $this->master->isDirectory($key);
    }
}
