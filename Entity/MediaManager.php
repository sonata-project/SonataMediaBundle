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

use Doctrine\ORM\EntityManager;

use Sonata\CoreBundle\Entity\DoctrineBaseManager;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaManager extends DoctrineBaseManager implements MediaManagerInterface
{
    /**
     * Constructor.
     * 
     * @param string        $class
     * @param EntityManager $em
     * @param Pool          $pool
     */
    public function __construct($class, EntityManager $em, Pool $pool)
    {
        $this->pool = $pool;

        parent::__construct($class, $em);
    }

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
