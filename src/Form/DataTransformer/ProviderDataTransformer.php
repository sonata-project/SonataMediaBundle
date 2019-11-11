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

namespace Sonata\MediaBundle\Form\DataTransformer;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ProviderDataTransformer implements DataTransformerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param string $class
     */
    public function __construct(Pool $pool, $class, array $options = [])
    {
        $this->pool = $pool;
        $this->options = $this->getOptions($options);
        $this->class = $class;

        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return new $this->class();
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

        // no binary
        if (empty($binaryContent)) {
            // and no media id
            if (null === $media->getId() && $this->options['empty_on_new']) {
                return;
            } elseif ($media->getId()) {
                return $media;
            }

            $media->setProviderStatus(MediaInterface::STATUS_PENDING);
            $media->setProviderReference(MediaInterface::MISSING_BINARY_REFERENCE);

            return $media;
        }

        // create a new media to avoid erasing other media or not ...
        $newMedia = $this->options['new_on_update'] ? new $this->class() : $media;

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

        try {
            $provider->transform($newMedia);
        } catch (\Throwable $e) {
            // #1107 We must never throw an exception here.
            // An exception here would prevent us to provide meaningful errors through the Form
            // Error message inspired from Monolog\ErrorHandler
            $this->logger->error(
                sprintf('Caught Exception %s: "%s" at %s line %s', \get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                ['exception' => $e]
            );
        }

        return $newMedia;
    }

    /**
     * Define the default options for the DataTransformer.
     *
     * @return array
     */
    protected function getOptions(array $options)
    {
        return array_merge([
            'provider' => false,
            'context' => false,
            'empty_on_new' => true,
            'new_on_update' => true,
        ], $options);
    }
}
