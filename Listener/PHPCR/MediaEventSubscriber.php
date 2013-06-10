<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Listener\PHPCR;

use Doctrine\Common\EventArgs;
use Doctrine\ODM\PHPCR\Event;
use Sonata\MediaBundle\Listener\BaseMediaEventSubscriber;

class MediaEventSubscriber extends BaseMediaEventSubscriber
{
    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            Event::prePersist,
            Event::preUpdate,
            Event::preRemove,
            Event::postUpdate,
            Event::postRemove,
            Event::postPersist,
        );
    }

    /**
     * @inheritdoc
     */
    protected function recomputeSingleEntityChangeSet(EventArgs $args)
    {
        /** @var $args \Doctrine\Common\Persistence\Event\LifecycleEventArgs */
        /** @var $dm \Doctrine\ODM\PHPCR\DocumentManager */
        $dm = $args->getObjectManager();

        $dm->getUnitOfWork()->computeSingleDocumentChangeSet(
            $this->getMedia($args)
        );
    }

    /**
     * @inheritdoc
     */
    protected function getMedia(EventArgs $args)
    {
        return $args->getObject();
    }
}
