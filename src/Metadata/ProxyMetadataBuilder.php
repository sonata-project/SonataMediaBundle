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
use Sonata\MediaBundle\Provider\Pool;

final class ProxyMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var MetadataBuilderInterface|null
     */
    private $noopMetadataBuilder;

    /**
     * @var MetadataBuilderInterface|null
     */
    private $amazonMetadataBuilder;

    public function __construct(
        Pool $pool,
        ?MetadataBuilderInterface $noopMetadataBuilder = null,
        ?MetadataBuilderInterface $amazonMetadataBuilder = null
    ) {
        $this->pool = $pool;
        $this->noopMetadataBuilder = $noopMetadataBuilder;
        $this->amazonMetadataBuilder = $amazonMetadataBuilder;
    }

    public function get(MediaInterface $media, string $filename): array
    {
        $providerName = $media->getProviderName();

        if (null === $providerName) {
            return [];
        }

        $meta = $this->getAmazonBuilder($media, $this->pool->getProvider($providerName), $filename);

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
