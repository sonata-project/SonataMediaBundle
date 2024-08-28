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

final class Server implements CDNInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    public function getPath(string $relativePath, bool $isFlushable = false): string
    {
        return \sprintf('%s/%s', $this->path, ltrim($relativePath, '/'));
    }

    public function flushByString(string $string): string
    {
        return '';
    }

    public function flush(string $string): string
    {
        return '';
    }

    public function flushPaths(array $paths): string
    {
        return '';
    }

    public function getFlushStatus(string $identifier): int
    {
        return CDNInterface::STATUS_OK;
    }
}
