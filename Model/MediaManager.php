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
     * {@inheritdoc}
     *
     * @return Media
     */
    public function create()
    {
        $class = $this->getClass();

        return new $class;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;
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
     * {@inheritdoc}
     *
     * @return MediaInterface[]
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     *
     * @return MediaInterface|null
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     *
     * @return MediaInterface[]
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Return the related Entity Repository.
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    abstract protected function getRepository();
}
