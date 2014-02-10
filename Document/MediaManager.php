<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

use Sonata\CoreBundle\Model\BaseDocumentManager;

class MediaManager extends BaseDocumentManager
{

    /**
     * {@inheritdoc}
     *
     * Warning: previous method signature was : save(MediaInterface $media, $context = null, $providerName = null)
     *
     * @throws \InvalidArgumentException When entity is an invalid object
     */
    public function save($entity, $andFlush = true)
    {
        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            $entity->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            $entity->setProviderName(func_get_arg(2));
        }

        if ($andFlush && is_bool($andFlush)) {
            parent::save($entity, $andFlush);
        } else {
            // BC compatibility with previous signature
            parent::save($entity, true);
        }
    }
}
