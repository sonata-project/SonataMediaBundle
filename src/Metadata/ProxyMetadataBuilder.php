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
        //get adapter for current media
        if (!$this->container->has($media->getProviderName())) {
            return [];
        }

        $meta = $this->getAmazonBuilder($media, $filename);

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
    private function getAmazonBuilder(MediaInterface $media, string $filename): ?array
    {
        $adapter = $this->container->get($media->getProviderName())->getFilesystem()->getAdapter();

        //handle special Replicate adapter
        if ($adapter instanceof Replicate) {
            $adapterClassNames = $adapter->getAdapterClassNames();
        } else {
            $adapterClassNames = [\get_class($adapter)];
        }

        //for amazon s3
        if (null === $this->amazonMetadataBuilder || !\in_array(AwsS3::class, $adapterClassNames, true)) {
            return null;
        }

        return $this->amazonMetadataBuilder->get($media, $filename);
    }
}
