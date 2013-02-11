<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Metadata;

use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Filesystem\Replicate;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProxyMetadataBuilder implements MetadataBuilderInterface
{
    private $container;
    private $map;
    private $metadata;

    /**
     * @param ContainerInterface $metadata
     * @param array              $map
     */
    public function __construct(ContainerInterface $container, array $map)
    {
        $this->container = $container;
        $this->map = $map;
    }

    /**
    * {@inheritdoc}
    */
    public function get(MediaInterface $media, $filename)
    {
        //get adapter for current media
        if (!$this->container->has($media->getProviderName())) {
            return array();
        }

        if ($meta = $this->getAmazonBuilder($media, $filename)) {
            return $meta;
        }

        if (!$this->container->has('sonata.media.metadata.noop')) {
            return array();
        }

        return $this->container->get('sonata.media.metadata.noop')->get($media, $filename);
    }

    /**
     * @param MediaInterface $metadata
     * @param string         $filename
     *
     * @return array
     */
    protected function getAmazonBuilder(MediaInterface $media, $filename)
    {
        $adapter = $this->container->get($media->getProviderName())->getFilesystem()->getAdapter();

        //handle special Replicate adapter
        if ($adapter instanceof Replicate) {
            $adapterClassNames = $adapter->getAdapterClassNames();
        } else {
            $adapterClassNames = array(get_class($adapter));
        }

        //for amazon s3
        if (!in_array('Gaufrette\Adapter\AmazonS3', $adapterClassNames) || !$this->container->has('sonata.media.metadata.amazon')) {
            return false;
        }

        return $this->container->get('sonata.media.metadata.amazon')->get($media, $filename);;
    }
}
