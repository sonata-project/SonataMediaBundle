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

namespace Sonata\MediaBundle\Filesystem;

use Gaufrette\Adapter as AdapterInterface;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Filesystem;
use Psr\Log\LoggerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Replicate implements AdapterInterface, MetadataSupporter
{
    /**
     * @var AdapterInterface
     *
     * @deprecated since version 3.31, to be removed in 4.0. Use `$primary` instead.
     */
    protected $master;

    /**
     * @var AdapterInterface
     *
     * @deprecated since version 3.31, to be removed in 4.0. Use `$secondary` instead.
     */
    protected $slave;
    /**
     * @var LoggerInterface
     *
     * NEXT_MAJOR change visibiity to private
     */
    protected $logger;

    /**
     * @var AdapterInterface
     */
    private $primary;

    /**
     * @var AdapterInterface
     */
    private $secondary;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(AdapterInterface $primary, AdapterInterface $secondary, ?LoggerInterface $logger = null)
    {
        // NEXT_MAJOR: remove master nad slave
        $this->master = $primary;
        $this->slave = $secondary;
        $this->primary = $primary;
        $this->secondary = $secondary;
        $this->logger = $logger;
    }

    public function delete($key)
    {
        $ok = true;

        try {
            $this->secondary->delete($key);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to delete %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        try {
            $this->primary->delete($key);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to delete %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        return $ok;
    }

    public function mtime($key)
    {
        return $this->primary->mtime($key);
    }

    public function keys()
    {
        return $this->primary->keys();
    }

    public function exists($key)
    {
        return $this->primary->exists($key);
    }

    public function write($key, $content, ?array $metadata = null)
    {
        $ok = true;
        $return = false;

        try {
            $return = $this->primary->write($key, $content, $metadata);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to write %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        try {
            $return = $this->secondary->write($key, $content, $metadata);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to write %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        return $ok && $return;
    }

    public function read($key)
    {
        return $this->primary->read($key);
    }

    public function rename($key, $new)
    {
        $ok = true;

        try {
            $this->primary->rename($key, $new);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to rename %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        try {
            $this->secondary->rename($key, $new);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to rename %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        return $ok;
    }

    /**
     * If one of the adapters can allow inserting metadata.
     *
     * @return bool true if supports metadata, false if not
     */
    public function supportsMetadata()
    {
        return $this->primary instanceof MetadataSupporter || $this->secondary instanceof MetadataSupporter;
    }

    public function setMetadata($key, $metadata): void
    {
        if ($this->primary instanceof MetadataSupporter) {
            $this->primary->setMetadata($key, $metadata);
        }
        if ($this->secondary instanceof MetadataSupporter) {
            $this->secondary->setMetadata($key, $metadata);
        }
    }

    public function getMetadata($key)
    {
        if ($this->primary instanceof MetadataSupporter) {
            return $this->primary->getMetadata($key);
        } elseif ($this->secondary instanceof MetadataSupporter) {
            return $this->secondary->getMetadata($key);
        }

        return [];
    }

    /**
     * Gets the class names as an array for both adapters.
     *
     * @return string[]
     */
    public function getAdapterClassNames()
    {
        return [
            \get_class($this->primary),
            \get_class($this->secondary),
        ];
    }

    public function createFile($key, Filesystem $filesystem)
    {
        return $this->primary->createFile($key, $filesystem);
    }

    public function createFileStream($key, Filesystem $filesystem)
    {
        return $this->primary->createFileStream($key, $filesystem);
    }

    public function listDirectory($directory = '')
    {
        return $this->primary->listDirectory($directory);
    }

    public function isDirectory($key)
    {
        return $this->primary->isDirectory($key);
    }
}
