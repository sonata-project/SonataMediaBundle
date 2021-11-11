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

namespace Sonata\MediaBundle\Model;

/**
 * @phpstan-template T of GalleryInterface
 * @phpstan-implements GalleryManagerInterface<T>
 */
abstract class GalleryManager implements GalleryManagerInterface
{
    public function create(): object
    {
        $class = $this->getClass();

        return new $class();
    }
}
