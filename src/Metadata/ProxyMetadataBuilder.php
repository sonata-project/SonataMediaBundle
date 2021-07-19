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

namespace Sonata\MediaBundle\Metadata;

use Gaufrette\Adapter\AwsS3;
use Sonata\MediaBundle\Filesystem\Replicate;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ProxyMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var MetadataBuilderInterface|null
     */
    private $noopMetadataBuilder;

    /**
     * @var MetadataBuilderInterface|null
     */
    private $amazonMetadataBuilder;

    public function __construct(
        ContainerInterface $container,
        ?MetadataBuilderInterface $noopMetadataBuilder = null,
        ?MetadataBuilderInterface $amazonMetadataBuilder = null
    ) {
        $this->container = $container;
        $this->noopMetadataBuilder = $noopMetadataBuilder;
        $this->amazonMetadataBuilder = $amazonMetadataBuilder;
    }

    public function get(MediaInterface $media, $filename)
    {
        $providerName = $media->getProviderName();

        if (null === $providerName || !$this->container->has($providerName)) {
            return [];
        }

        $provider = $this->container->get($providerName);

        if (!$provider instanceof MediaProviderInterface) {
            throw new \RuntimeException(sprintf(
                'Provider %s for media %s does not implement %s.',
                $providerName,
                $media->getId(),
                MediaProviderInterface::class
            ));
        }

        $meta = $this->getAmazonBuilder($media, $provider, $filename);

        if (null !== $meta) {
            return $meta;
        }

        if (null === $this->noopMetadataBuilder) {
            return [];
        }

        return $this->noopMetadataBuilder->get($media, $filename);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getAmazonBuilder(MediaInterface $media, MediaProviderInterface $provider, string $filename): ?array
    {
        $adapter = $provider->getFilesystem()->getAdapter();

        // Handle special Replicate adapter
        if ($adapter instanceof Replicate) {
            $adapterClassNames = $adapter->getAdapterClassNames();
        } else {
            $adapterClassNames = [\get_class($adapter)];
        }

        // For amazon s3
        if (null === $this->amazonMetadataBuilder || !\in_array(AwsS3::class, $adapterClassNames, true)) {
            return null;
        }

        return $this->amazonMetadataBuilder->get($media, $filename);
    }
}
