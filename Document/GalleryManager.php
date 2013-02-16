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

use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;

class GalleryManager extends AbstractGalleryManager
{
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
        $this->dm->persist($gallery);
        $this->dm->flush();
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
    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(GalleryInterface $gallery)
    {
        $this->dm->remove($gallery);
        $this->dm->flush();
    }
}
