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
use Gaufrette\Adapter\FileFactory;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Adapter\StreamFactory;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Psr\Log\LoggerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Replicate implements AdapterInterface, FileFactory, MetadataSupporter, StreamFactory
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
     * NEXT_MAJOR change visibility to private
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

    public function __construct(AdapterInterface $primary, AdapterInterface $secondary, ?LoggerInterface $logger = null)
    {
        // NEXT_MAJOR: remove master and slave.
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

    /**
     * NEXT_MAJOR: Remove argument 3.
     */
    public function write($key, $content, ?array $metadata = null)
    {
        if (3 === \func_num_args()) {
            @trigger_error(sprintf(
                'Argument 3 in "%s()" method is deprecated since sonata-project/media-bundle 3.33'
                .' and will be removed in version 4.0.',
                __METHOD__
            ), \E_USER_DEPRECATED);
        }

        $ok = true;
        $return = false;

        try {
            $return = $this->primary->write($key, $content);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to write %s, error: %s', $key, $e->getMessage()));
            }

            $ok = false;
        }

        try {
            $return = $this->secondary->write($key, $content);
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

    public function rename($sourceKey, $targetKey)
    {
        $ok = true;

        try {
            $this->primary->rename($sourceKey, $targetKey);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to rename %s, error: %s', $sourceKey, $e->getMessage()));
            }

            $ok = false;
        }

        try {
            $this->secondary->rename($sourceKey, $targetKey);
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->critical(sprintf('Unable to rename %s, error: %s', $sourceKey, $e->getMessage()));
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

    public function setMetadata($key, $content)
    {
        if ($this->primary instanceof MetadataSupporter) {
            $this->primary->setMetadata($key, $content);
        }
        if ($this->secondary instanceof MetadataSupporter) {
            $this->secondary->setMetadata($key, $content);
        }
    }

    public function getMetadata($key)
    {
        if ($this->primary instanceof MetadataSupporter) {
            return $this->primary->getMetadata($key);
        }

        if ($this->secondary instanceof MetadataSupporter) {
            return $this->secondary->getMetadata($key);
        }

        return [];
    }

    /**
     * Gets the class names as an array for both adapters.
     *
     * @return string[]
     *
     * @phpstan-return class-string<AdapterInterface>[]
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
        if ($this->primary instanceof FileFactory) {
            return $this->primary->createFile($key, $filesystem);
        }

        if ($this->secondary instanceof FileFactory) {
            return $this->secondary->createFile($key, $filesystem);
        }

        throw new \LogicException(sprintf('None of the adapters implement %s.', FileFactory::class));
    }

    /**
     * @param string $key
     *
     * @return Stream
     */
    public function createStream($key)
    {
        if ($this->primary instanceof StreamFactory) {
            return $this->primary->createStream($key);
        }

        if ($this->secondary instanceof StreamFactory) {
            return $this->secondary->createStream($key);
        }

        throw new \LogicException(sprintf('None of the adapters implement %s.', StreamFactory::class));
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.33, use "createStream()" instead.
     *
     * @param string $key
     *
     * @return Stream
     */
    public function createFileStream($key, Filesystem $filesystem)
    {
        @trigger_error(sprintf(
            'Method "%s()" is deprecated since sonata-project/media-bundle 3.33 and will be removed'
            .' in version 4.0. Use "createStream()" instead.',
            __METHOD__
        ), \E_USER_DEPRECATED);

        return $this->createStream($key);
    }

    public function listDirectory($directory = '')
    {
        if (!method_exists($this->primary, 'listDirectory')) {
            throw new \BadMethodCallException(sprintf(
                'Method "%s()" is not supported by the primary adapter "%s".',
                __METHOD__,
                \get_class($this->primary)
            ));
        }

        return $this->primary->listDirectory($directory);
    }

    public function isDirectory($key)
    {
        return $this->primary->isDirectory($key);
    }
}
