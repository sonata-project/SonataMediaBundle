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

        if(class_exists($class)) {
            $this->repository = $this->em->getRepository($class);
        }
    }
    
    /**
     * Updates a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    public function updateGallery(GalleryInterface $gallery)
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
    public function findGalleryBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Finds galleries by the given criteria
     *
     * @param array $criteria
     * @return array
     */
    public function findGalleriesBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Deletes a gallery
     *
     * @param Gallery $gallery
     * @return void
     */
    public function deleteGallery(GalleryInterface $gallery)
    {
        $this->em->remove($gallery);
        $this->em->flush();
    }

}