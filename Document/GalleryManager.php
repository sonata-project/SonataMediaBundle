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

use Doctrine\DBAL\Connection;
use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class GalleryManager extends AbstractGalleryManager
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    protected $repository;
    protected $class;

    /**
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param string                                $class
     */
    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm    = $dm;
        $this->class = $class;
    }

    /**
     * @return mixed
     */
    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->dm->getRepository($this->class);
        }

        return $this->repository;
    }

    /**
     * {@inheritdoc}
     */
    public function update(GalleryInterface $gallery)
    {
        $this->save($gallery, true);
    }

    /**
     * {@inheritdoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->getRepository()->findOneBy($criteria, $orderBy);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($gallery, $andFlush = true)
    {
        $this->dm->remove($gallery);
        if ($andFlush) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        return $this->getRepository()->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function save($entity, $andFlush = true)
    {
        $this->dm->persist($entity);
        if ($andFlush) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        return $this->dm->getClassMetadata($this->class)->table['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection()
    {
        return $this->dm->getConnection();
    }
}
