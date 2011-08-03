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
    protected $pool;

    public function __construct(Pool $pool)
    {
        $this->pool = $pool;
    }

    /**
     * Creates an empty media instance
     *
     * @return Media
     */
    function create()
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
}