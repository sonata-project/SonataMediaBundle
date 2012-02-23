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

    /**
     * @param \Sonata\MediaBundle\Provider\Pool $pool
     * @param \Doctrine\ORM\EntityManager $em
     * @param $class
     */
    public function __construct(Pool $pool, EntityManager $em, $class)
    {
        $this->em    = $em;

        parent::__construct($pool, $class);
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
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $context
     * @param string $providerName
     * @return void
     */
    public function save(MediaInterface $media, $context = null, $providerName = null)
    {
        if ($context) {
            $media->setContext($context);
        }

        if ($providerName) {
            $media->setProviderName($providerName);
        }

        // just in case the pool alter the media
        $this->em->persist($media);
        $this->em->flush();
    }

    /**
     * Deletes a media
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function delete(MediaInterface $media)
    {
        $this->em->remove($media);
        $this->em->flush();
    }
}