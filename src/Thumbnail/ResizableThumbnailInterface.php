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

namespace Sonata\MediaBundle\Thumbnail;

use Sonata\MediaBundle\Resizer\ResizerInterface;

/**
 * @author Jordi Sala Morales <jordism91@gmail.com>
 */
interface ResizableThumbnailInterface
{
    public function addResizer(string $id, ResizerInterface $resizer): void;

    public function hasResizer(string $id): bool;

    /**
     * @throws \LogicException if resizer is not found
     */
    public function getResizer(string $id): ResizerInterface;
}
