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

interface ResizableThumbnailInterface
{
    public function addResizer(string $id, ResizerInterface $resizer): void;

    /**
     * @throws \LogicException if resizer is not found
     */
    public function getResizer(string $id): ResizerInterface;
}
