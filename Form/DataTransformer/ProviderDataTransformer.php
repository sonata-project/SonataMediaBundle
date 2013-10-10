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
        $this->options = $this->getOptions($options);
        $this->class   = $class;

    }

    /**
     * Define the default options for the DataTransformer
     *
     * @param array $options
     * @return array
     */
    protected function getOptions(array $options) {

        return array_merge(array(
            'provider'      => false,
            'context'       => false,
            'empty_on_new'  => true,
            'new_on_update' => true,
        ), $options);
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

        // no binary content and no media id
        if (empty($binaryContent) && $media->getId() === null) {
            if ($this->options['empty_on_new']) {
                return null;
            }

            return $media;
        }

        // no update, but the the media exists ...
        if (empty($binaryContent) && $media->getId() !== null) {
            return $media;
        }

        // create a new media to avoid erasing other media or not ...
        $newMedia = $this->options['new_on_update'] ? new $this->class : $media;

        $newMedia->setProviderName($media->getProviderName());
        $newMedia->setContext($media->getContext());
        $newMedia->setBinaryContent($binaryContent);

        if (!$newMedia->getProviderName() && $this->options['provider']) {
            $newMedia->setProviderName($this->options['provider']);
        }

        if (!$newMedia->getContext() && $this->options['context']) {
            $newMedia->setContext($this->options['context']);
        }

        $provider = $this->pool->getProvider($newMedia->getProviderName());

        $provider->transform($newMedia);

        return $newMedia;
    }
}
