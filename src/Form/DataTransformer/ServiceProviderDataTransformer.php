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
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ServiceProviderDataTransformer implements DataTransformerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var MediaProviderInterface
     */
    protected $provider;

    public function __construct(MediaProviderInterface $provider)
    {
        $this->provider = $provider;

        $this->logger = new NullLogger();
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

        try {
            $this->provider->transform($media);
        } catch (\Throwable $e) {
            // #1107 We must never throw an exception here.
            // An exception here would prevent us to provide meaningful errors through the Form
            // Error message taken from Monolog\ErrorHandler
            $this->logger->error(
                sprintf('Caught Exception %s: "%s" at %s line %s', \get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()),
                ['exception' => $e]
            );
        }

        return $media;
    }
}
