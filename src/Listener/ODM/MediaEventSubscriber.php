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

namespace Sonata\MediaBundle\Listener\ODM;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs as ODMLifecycleEventArgs;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;

final class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove,
            Events::postUpdate,
            Events::postRemove,
            Events::postPersist,
        ];
    }

    protected function recomputeSingleEntityChangeSet(LifecycleEventArgs $args): void
    {
        \assert($args instanceof ODMLifecycleEventArgs);

        $em = $args->getDocumentManager();

        $em->getUnitOfWork()->recomputeSingleDocumentChangeSet(
            $em->getClassMetadata(\get_class($args->getObject())),
            $args->getObject()
        );
    }

    protected function getMedia(LifecycleEventArgs $args)
    {
        $media = $args->getObject();

        if (!$media instanceof MediaInterface) {
            throw new \LogicException('There is no media on the persistence event.');
        }

        return $media;
    }
}
