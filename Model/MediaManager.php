<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

use Sonata\MediaBundle\Provider\Pool;

abstract class MediaManager implements MediaManagerInterface
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     * @param string                            $class
     */
    public function __construct(Pool $pool, $class)
    {
        $this->pool  = $pool;
        $this->class = $class;
    }

    /**
     * Creates an empty media instance
     *
     * @return Media
     */
    public function create()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * return the provider pool
     *
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     *
     * @return Media
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     *
     * @return Media
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }
}
