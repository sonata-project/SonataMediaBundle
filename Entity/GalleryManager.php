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

use Sonata\MediaBundle\Model\GalleryManager as AbstractGalleryManager;
use Sonata\MediaBundle\Model\GalleryInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\MediaBundle\Provider\Pool;

class GalleryManager extends AbstractGalleryManager
{
    protected $em;
    protected $repository;
    protected $class;

    public function __construct(EntityManager $em, $class)
    {
        $this->em    = $em;
        $this->class = $class;
    }

    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository($this->class);
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
        $this->em->persist($gallery);
        $this->em->flush();
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
        $this->em->remove($gallery);
        $this->em->flush();
    }
}