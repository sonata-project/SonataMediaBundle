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

namespace Sonata\MediaBundle\LiipImagine;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

interface ResolverRegistryInterface
{
    /**
     * @param string $name
     *
     * @throws \OutOfBoundsException when resolver cannot be found in the registry
     *
     * @return ResolverInterface
     */
    public function getResolver($name);
}
