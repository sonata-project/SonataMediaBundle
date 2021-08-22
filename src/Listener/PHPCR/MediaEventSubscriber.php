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

namespace Sonata\MediaBundle\Listener\PHPCR;

use Doctrine\Common\EventArgs;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Event;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;
use Sonata\MediaBundle\Model\MediaInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Event::prePersist,
            Event::preUpdate,
            Event::preRemove,
            Event::postUpdate,
            Event::postRemove,
            Event::postPersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        /** @var DocumentManager $dm */
        $dm = $args->getObjectManager();

        $dm->getUnitOfWork()->computeSingleDocumentChangeSet(
            $this->getMedia($args)
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    protected function getMedia(EventArgs $args)
    {
        $media = $args->getObject();

        if (!$media instanceof MediaInterface) {
            return null;
        }

        return $media;
    }
}
