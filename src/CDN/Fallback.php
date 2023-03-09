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

final class Fallback implements CDNInterface
{
    public function __construct(
        private CDNInterface $cdn,
        private CDNInterface $fallback
    ) {
    }

    public function getPath(string $relativePath, bool $isFlushable = false): string
    {
        if ($isFlushable) {
            return $this->fallback->getPath($relativePath, $isFlushable);
        }

        return $this->cdn->getPath($relativePath, $isFlushable);
    }

    public function flushByString(string $string): string
    {
        return $this->cdn->flushByString($string);
    }

    public function flush(string $string): string
    {
        return $this->cdn->flush($string);
    }

    public function flushPaths(array $paths): string
    {
        return $this->cdn->flushPaths($paths);
    }

    public function getFlushStatus(string $identifier): int
    {
        return $this->cdn->getFlushStatus($identifier);
    }
}
