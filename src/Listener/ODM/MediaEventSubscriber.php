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

use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Events;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        $em = $args->getDocumentManager();

        $em->getUnitOfWork()->recomputeSingleDocumentChangeSet(
            $em->getClassMetadata(\get_class($args->getDocument())),
            $args->getDocument()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getMedia(EventArgs $args)
    {
        return $args->getDocument();
    }
}
