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

use Gaufrette\Adapter\AmazonS3;
use Gaufrette\Adapter\AwsS3;
use Sonata\MediaBundle\Filesystem\Replicate;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ProxyMetadataBuilder implements MetadataBuilderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * NEXT_MAJOR: remove the second parameter $map.
     *
     * @param array $map
     */
    public function __construct(ContainerInterface $container, array $map = null)
    {
        $this->container = $container;

        if (null !== $map) {
            @trigger_error(
                'The "map" parameter is deprecated since sonata-project/media-bundle 2.4 and will be removed in 4.0.',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(MediaInterface $media, $filename)
    {
        //get adapter for current media
        if (!$this->container->has($media->getProviderName())) {
            return [];
        }

        if ($meta = $this->getAmazonBuilder($media, $filename)) {
            return $meta;
        }

        if (!$this->container->has('sonata.media.metadata.noop')) {
            return [];
        }

        return $this->container->get('sonata.media.metadata.noop')->get($media, $filename);
    }

    /**
     * @param string $filename
     *
     * @return array|bool
     */
    protected function getAmazonBuilder(MediaInterface $media, $filename)
    {
        $adapter = $this->container->get($media->getProviderName())->getFilesystem()->getAdapter();

        //handle special Replicate adapter
        if ($adapter instanceof Replicate) {
            $adapterClassNames = $adapter->getAdapterClassNames();
        } else {
            $adapterClassNames = [\get_class($adapter)];
        }

        //for amazon s3
        if ((!\in_array(AmazonS3::class, $adapterClassNames, true) && !\in_array(AwsS3::class, $adapterClassNames, true)) || !$this->container->has('sonata.media.metadata.amazon')) {
            return false;
        }

        return $this->container->get('sonata.media.metadata.amazon')->get($media, $filename);
    }
}
