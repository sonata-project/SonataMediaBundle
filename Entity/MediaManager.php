<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Entity;

use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\MediaBundle\Model\MediaManagerInterface;

class MediaManager extends BaseEntityManager implements MediaManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($media, $andFlush = true)
    {
        /*
         * Warning: previous method signature was : save(MediaInterface $media, $context = null, $providerName = null)
         */

        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            $media->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            $media->setProviderName(func_get_arg(2));
        }

        if ($andFlush && is_bool($andFlush)) {
            parent::save($media, $andFlush);
        } else {
            // BC compatibility with previous signature
            parent::save($media, true);
        }
    }
}
