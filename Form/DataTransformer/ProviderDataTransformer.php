<?php
/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Model\MediaInterface;

class ProviderDataTransformer implements DataTransformerInterface
{
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($media)
    {
        if (!$media instanceof MediaInterface) {
            return $media;
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $provider->transform($media);

        return $media;
    }
}