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

    protected $options;

    /**
     * @param Pool   $pool
     * @param string $class
     * @param array  $options
     */
    public function __construct(Pool $pool, $class, array $options = array())
    {
        $this->pool    = $pool;
        $this->options = $options;
        $this->class   = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
            return new $this->class;
        }

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

        $binaryContent = $media->getBinaryContent();

        // no update, but the the media exists ...
        if (empty($binaryContent) && $media->getId() !== null) {
            return $media;
        }

        if (!$media->getProviderName() && isset($this->options['provider'])) {
            $media->setProviderName($this->options['provider']);
        }

        if (!$media->getContext() && isset($this->options['context'])) {
            $media->setContext($this->options['context']);
        }

        $provider = $this->pool->getProvider($media->getProviderName());

        $provider->transform($media);

        return $media;
    }
}
