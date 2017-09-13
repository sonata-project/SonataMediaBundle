<?php

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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\DataTransformerInterface;

class ProviderDataTransformer implements DataTransformerInterface, LoggerAwareInterface
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var array
     */
    protected $options;

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Pool   $pool
     * @param string $class
     * @param array  $options
     */
    public function __construct(Pool $pool, $class, array $options = array())
    {
        $this->pool = $pool;
        $this->options = $this->getOptions($options);
        $this->class = $class;

        $this->logger = new NullLogger();
    }

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value === null) {
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
            if ($media->getId() === null && $this->options['empty_on_new']) {
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
        } catch (\Exception $e) { // NEXT_MAJOR: When switching to PHP 7+, change this to \Throwable
            // #1107 We must never throw an exception here.
            // An exception here would prevent us to provide meaningful errors through the Form
            // Error message inspired from Monolog\ErrorHandler
            $this->logger->error(
                sprintf('Caught Exception %s: "%s" at %s line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                array('exception' => $e)
            );
        }

        return $newMedia;
    }

    /**
     * Define the default options for the DataTransformer.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getOptions(array $options)
    {
        return array_merge(array(
            'provider' => false,
            'context' => false,
            'empty_on_new' => true,
            'new_on_update' => true,
        ), $options);
    }
}
