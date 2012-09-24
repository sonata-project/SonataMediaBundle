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

    protected $array;

    /**
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     * @param array $options
     */
    public function __construct(Pool $pool, array $options = array())
    {
        $this->pool    = $pool;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($media)
    {
        if (!$media instanceof MediaInterface) {
            return $media;
        }

        if (!$media->getProviderName() || isset($this->options['provider'])) {
            $media->setProviderName($this->options['provider']);
        }

        if (!$media->getContext() || isset($this->options['context'])) {
            $media->setContext($this->options['context']);
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $provider->transform($media);

        return $media;
    }
}