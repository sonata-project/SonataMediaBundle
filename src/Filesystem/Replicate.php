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

use Gaufrette\Adapter;
use Gaufrette\Adapter\FileFactory;
use Gaufrette\Adapter\MetadataSupporter;
use Gaufrette\Adapter\StreamFactory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Gaufrette\Stream;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class Replicate implements Adapter, FileFactory, StreamFactory, MetadataSupporter
{
    private LoggerInterface $logger;

    public function __construct(
        private Adapter $primary,
        private Adapter $secondary,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function delete($key): bool
    {
        $ok = true;

        try {
            $this->secondary->delete($key);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to delete %s, error: %s', $key, $e->getMessage()));

            $ok = false;
        }

        try {
            $this->primary->delete($key);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to delete %s, error: %s', $key, $e->getMessage()));

            $ok = false;
        }

        return $ok;
    }

    /**
     * @param string $key
     *
     * @return int|bool
     */
    public function mtime($key)
    {
        return $this->primary->mtime($key);
    }

    /**
     * @return string[]
     */
    public function keys(): array
    {
        return $this->primary->keys();
    }

    public function exists($key): bool
    {
        return $this->primary->exists($key);
    }

    /**
     * @param string $key
     * @param string $content
     *
     * @return bool
     */
    public function write($key, $content)
    {
        $ok = true;
        $return = false;

        try {
            $return = $this->primary->write($key, $content);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to write %s, error: %s', $key, $e->getMessage()));

            $ok = false;
        }

        try {
            $return = $this->secondary->write($key, $content);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to write %s, error: %s', $key, $e->getMessage()));

            $ok = false;
        }

        return $ok && false !== $return;
    }

    /**
     * @param string $key
     *
     * @return string|bool
     */
    public function read($key)
    {
        return $this->primary->read($key);
    }

    public function rename($sourceKey, $targetKey): bool
    {
        $ok = true;

        try {
            $this->primary->rename($sourceKey, $targetKey);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to rename %s, error: %s', $sourceKey, $e->getMessage()));

            $ok = false;
        }

        try {
            $this->secondary->rename($sourceKey, $targetKey);
        } catch (\Exception $e) {
            $this->logger->critical(\sprintf('Unable to rename %s, error: %s', $sourceKey, $e->getMessage()));

            $ok = false;
        }

        return $ok;
    }

    /**
     * If one of the adapters can allow inserting metadata.
     */
    public function supportsMetadata(): bool
    {
        return $this->primary instanceof MetadataSupporter || $this->secondary instanceof MetadataSupporter;
    }

    /**
     * @param string  $key
     * @param mixed[] $content
     */
    public function setMetadata($key, $content): void
    {
        if ($this->primary instanceof MetadataSupporter) {
            $this->primary->setMetadata($key, $content);
        }

        if ($this->secondary instanceof MetadataSupporter) {
            $this->secondary->setMetadata($key, $content);
        }
    }

    /**
     * @param string $key
     *
     * @return mixed[]
     */
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
     * @phpstan-return class-string<Adapter>[]
     */
    public function getAdapterClassNames(): array
    {
        return [
            $this->primary::class,
            $this->secondary::class,
        ];
    }

    public function createFile($key, Filesystem $filesystem): File
    {
        if ($this->primary instanceof FileFactory) {
            return $this->primary->createFile($key, $filesystem);
        }

        if ($this->secondary instanceof FileFactory) {
            return $this->secondary->createFile($key, $filesystem);
        }

        throw new \LogicException(\sprintf('None of the adapters implement %s.', FileFactory::class));
    }

    /**
     * @param string $key
     */
    public function createStream($key): Stream
    {
        if ($this->primary instanceof StreamFactory) {
            return $this->primary->createStream($key);
        }

        if ($this->secondary instanceof StreamFactory) {
            return $this->secondary->createStream($key);
        }

        throw new \LogicException(\sprintf('None of the adapters implement %s.', StreamFactory::class));
    }

    /**
     * @return array<string, string[]>
     *
     * @phpstan-return array{keys: string[], dirs: string[]}
     */
    public function listDirectory(string $directory = ''): array
    {
        if (!method_exists($this->primary, 'listDirectory')) {
            throw new \BadMethodCallException(\sprintf(
                'Method "%s()" is not supported by the primary adapter "%s".',
                __METHOD__,
                $this->primary::class
            ));
        }

        return $this->primary->listDirectory($directory);
    }

    public function isDirectory($key): bool
    {
        return $this->primary->isDirectory($key);
    }
}
