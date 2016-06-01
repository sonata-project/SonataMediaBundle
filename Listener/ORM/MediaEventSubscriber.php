<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener\ORM;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;

class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @var CategoryInterface[]
     */
    protected $rootCategories;

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,
            Events::onClear,
        );
    }

    public function onClear()
    {
        $this->rootCategories = null;
    }

    /**
     * {@inheritdoc}
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        $em = $args->getEntityManager();

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(get_class($args->getEntity())),
            $args->getEntity()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getMedia(EventArgs $args)
    {
        $media = $args->getEntity();

        if (!$media instanceof MediaInterface) {
            return $media;
        }

        if (!$media->getCategory()) {
            $media->setCategory($this->getRootCategory($media));
        }

        return $media;
    }

    /**
     * @param MediaInterface $media
     *
     * @return CategoryInterface
     *
     * @throws \RuntimeException
     */
    protected function getRootCategory(MediaInterface $media)
    {
        if (!$this->rootCategories) {
            $this->rootCategories = $this->container->get('sonata.classification.manager.category')->getRootCategories(false);
        }

        if (!array_key_exists($media->getContext(), $this->rootCategories)) {
            throw new \RuntimeException(sprintf('There is no main category related to context: %s', $media->getContext()));
        }

        return $this->rootCategories[$media->getContext()];
    }
}
