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

namespace Sonata\MediaBundle\Listener\ORM;

use Doctrine\ORM\Event\LifecycleEventArgs as ORMLifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;

final class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @var CategoryManagerInterface|null
     */
    private $categoryManager;

    /**
     * @var CategoryInterface[]|null
     */
    private $rootCategories;

    public function __construct(Pool $pool, ?CategoryManagerInterface $categoryManager = null)
    {
        parent::__construct($pool);

        $this->categoryManager = $categoryManager;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,
            Events::onClear,
        ];
    }

    public function onClear(): void
    {
        $this->rootCategories = null;
    }

    protected function recomputeSingleEntityChangeSet(LifecycleEventArgs $args): void
    {
        \assert($args instanceof ORMLifecycleEventArgs);

        $em = $args->getEntityManager();

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata(\get_class($args->getObject())),
            $args->getObject()
        );
    }

    protected function getMedia(LifecycleEventArgs $args)
    {
        $media = $args->getObject();

        if (!$media instanceof MediaInterface) {
            throw new \RuntimeException('There is no media on the persistence event.');
        }

        if (null !== $this->categoryManager && !$media->getCategory()) {
            $media->setCategory($this->getRootCategory($media));
        }

        return $media;
    }

    /**
     * @throws \RuntimeException
     *
     * @return CategoryInterface
     */
    protected function getRootCategory(MediaInterface $media)
    {
        if (!$this->rootCategories) {
            $this->rootCategories = $this->categoryManager->getRootCategories(false);
        }

        if (!\array_key_exists($media->getContext(), $this->rootCategories)) {
            throw new \RuntimeException(sprintf('There is no main category related to context: %s', $media->getContext()));
        }

        return $this->rootCategories[$media->getContext()];
    }
}
