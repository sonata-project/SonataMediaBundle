<?php

namespace Sonata\MediaBundle\LiipImagine;

use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

interface ResolverRegistryInterface
{
    /**
     * @param string $name
     *
     * @return ResolverInterface
     * @throws \OutOfBoundsException when resolver cannot be found in the registry
     */
    public function getResolver($name);
}
