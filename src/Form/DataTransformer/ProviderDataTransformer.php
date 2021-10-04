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

final class ProviderDataTransformer implements DataTransformerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var string
     *
     * @phpstan-var class-string<MediaInterface>
     */
    private $class;

    /**
     * @var array<string, mixed>
     */
    private $options;

    /**
     * @param array<string, mixed> $options
     *
     * @phpstan-param class-string<MediaInterface> $class
     */
    public function __construct(Pool $pool, string $class, array $options = [])
    {
        $this->pool = $pool;
        $this->options = $this->getOptions($options);
        $this->class = $class;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function transform($value)
    {
        if (null === $value) {
            return new $this->class();
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function reverseTransform($value)
    {
        if (!$value instanceof MediaInterface) {
            return $value;
        }

        $binaryContent = $value->getBinaryContent();

        // no binary
        if (null === $binaryContent) {
            // and no media id
            if (null === $value->getId() && true === $this->options['empty_on_new']) {
                return;
            }
            if (null !== $value->getId()) {
                return $value;
            }

            $value->setProviderStatus(MediaInterface::STATUS_PENDING);
            $value->setProviderReference(MediaInterface::MISSING_BINARY_REFERENCE);

            return $value;
        }

        // create a new media to avoid erasing other media or not ...
        $newMedia = true === $this->options['new_on_update'] ? new $this->class() : $value;

        $newMedia->setProviderName($value->getProviderName());
        $newMedia->setContext($value->getContext());
        $newMedia->setBinaryContent($binaryContent);

        if (null === $newMedia->getProviderName() && false !== $this->options['provider']) {
            $newMedia->setProviderName($this->options['provider']);
        }

        if (null === $newMedia->getContext() && false !== $this->options['context']) {
            $newMedia->setContext($this->options['context']);
        }

        $provider = $this->pool->getProvider($newMedia->getProviderName());

        try {
            $provider->transform($newMedia);
        } catch (\Throwable $e) {
            $logger = $this->logger ?? new NullLogger();

            // #1107 We must never throw an exception here.
            // An exception here would prevent us to provide meaningful errors through the Form
            // Error message inspired from Monolog\ErrorHandler
            $logger->error(
                sprintf('Caught Exception %s: "%s" at %s line %s', \get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                ['exception' => $e]
            );
        }

        return $newMedia;
    }

    /**
     * Define the default options for the DataTransformer.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function getOptions(array $options)
    {
        return array_merge([
            'provider' => false,
            'context' => false,
            'empty_on_new' => true,
            'new_on_update' => true,
        ], $options);
    }
}
