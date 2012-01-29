<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Document\PHPCR;

use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\Pool;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;

class GalleryManager extends AbstractGalleryManager
{
    protected $dm;
    protected $repository;
    protected $class;

    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm    = $dm;
        $this->class = $class;
    }

    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->dm->getRepository($this->class);
        }

        return $this->repository;
    }
    /**
     * Updates a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    public function update(GalleryInterface $gallery)
    {
        $this->dm->persist($gallery);
        $this->dm->flush();
    }

    /**
     * Returns the gallery's fully qualified class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Finds one gallery by the given criteria
     *
     * @param array $criteria
     * @return Gallery
     */
    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Finds galleries by the given criteria
     *
     * @param array $criteria
     * @return array
     */
    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * Deletes a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    public function delete(GalleryInterface $gallery)
    {
        $this->dm->remove($gallery);
        $this->dm->flush();
    }
}