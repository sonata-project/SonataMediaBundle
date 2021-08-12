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

namespace Sonata\MediaBundle\Listener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseMediaEventSubscriber implements EventSubscriber
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Pool
     */
    public function getPool()
    {
        return $this->container->get('sonata.media.pool');
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function postUpdate(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($args)->postUpdate($media);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function postRemove(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($args)->postRemove($media);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function postPersist(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($args)->postPersist($media);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function preUpdate(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $provider = $this->getProvider($args);

        $provider->transform($media);
        $provider->preUpdate($media);

        $this->recomputeSingleEntityChangeSet($args);
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function preRemove(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $this->getProvider($args)->preRemove($this->getMedia($args));
    }

    /**
     * @final since sonata-project/media-bundle 3.36.0
     */
    public function prePersist(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (null === $media) {
            return;
        }

        $provider = $this->getProvider($args);

        $provider->transform($media);
        $provider->prePersist($media);
    }

    abstract protected function recomputeSingleEntityChangeSet(EventArgs $args);

    /**
     * @return MediaInterface|null
     */
    abstract protected function getMedia(EventArgs $args);

    /**
     * @final since sonata-project/media-bundle 3.36.0
     *
     * @return MediaProviderInterface|null
     */
    protected function getProvider(EventArgs $args)
    {
        $media = $this->getMedia($args);

        if (!$media instanceof MediaInterface) {
            return null;
        }

        return $this->getPool()->getProvider($media->getProviderName());
    }
}
