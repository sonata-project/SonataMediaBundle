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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;

/**
 * @phpstan-extends BaseMediaEventSubscriber<EntityManagerInterface>
 */
final class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @var CategoryInterface[]|null
     */
    private ?array $rootCategories = null;

    public function __construct(
        Pool $pool,
        private ?CategoryManagerInterface $categoryManager = null
    ) {
        parent::__construct($pool);
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function getSubscribedEvents(): array
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
        $em = $args->getObjectManager();

        $em->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $em->getClassMetadata($args->getObject()::class),
            $args->getObject()
        );
    }

    protected function getMedia(LifecycleEventArgs $args): ?MediaInterface
    {
        $media = $args->getObject();

        if (!$media instanceof MediaInterface) {
            return null;
        }

        if (null !== $this->categoryManager && null === $media->getCategory()) {
            $media->setCategory($this->getRootCategory($media));
        }

        return $media;
    }

    /**
     * @throws \RuntimeException
     */
    private function getRootCategory(MediaInterface $media): CategoryInterface
    {
        if (null === $this->rootCategories && null !== $this->categoryManager) {
            $this->rootCategories = $this->categoryManager->getAllRootCategories(false);
        }

        $context = $media->getContext();

        if (null === $context) {
            throw new \RuntimeException(\sprintf('There is no context on media %s', $media->getId() ?? ''));
        }

        if (null !== $this->rootCategories) {
            foreach ($this->rootCategories as $category) {
                $categoryContext = $category->getContext();

                if (null !== $categoryContext && $categoryContext->getId() === $context) {
                    return $category;
                }
            }
        }

        throw new \RuntimeException(\sprintf('There is no main category related to context: %s', $context));
    }
}
