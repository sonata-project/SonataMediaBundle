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

use Sonata\MediaBundle\Model\MediaManager as AbstractMediaManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\MediaBundle\Provider\Pool;

class MediaManager extends AbstractMediaManager
{
    protected $em;
    protected $repository;
    protected $class;

    public function __construct(Pool $pool, EntityManager $em, $class)
    {
        $this->em    = $em;
        $this->class = $class;

        parent::__construct($pool);
    }

    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->em->getRepository($this->class);
        }

        return $this->repository;
    }

    /**
     * Updates a media
     *
     * @param Media $media
     * @return void
     */
    public function update(MediaInterface $media)
    {
        $this->em->persist($media);
        $this->em->flush();
    }

    /**
     * Returns the media's fully qualified class name
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     * @return Media
     */
    public function findOneBy(array $criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     * @return Media
     */
    public function findBy(array $criteria)
    {
        return $this->getRepository()->findBy($criteria);
    }

    /**
     * Deletes a media
     *
     * @param Media $media
     * @return void
     */
    public function delete(MediaInterface $media)
    {
        $this->em->remove($media);
        $this->em->flush();
    }
}