<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\PHPCR;

use Sonata\MediaBundle\Model\MediaManager as AbstractMediaManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\DocumentRepository;

use Sonata\MediaBundle\Provider\Pool;

class MediaManager extends AbstractMediaManager
{
    protected $dm;
    protected $repository;
    protected $class;

    /**
     * @param \Sonata\MediaBundle\Provider\Pool     $pool
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param string                                $class
     */
    public function __construct(Pool $pool, DocumentManager $dm, $class)
    {
        $this->dm    = $dm;

        parent::__construct($pool, $class);
    }

    protected function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->dm->getRepository($this->class);
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

        $isNew = $media->getId() != null;

        if ($isNew) {
            $this->pool->getProvider($media->getProviderName())->prePersist($media);
        } else {
            $this->pool->getProvider($media->getProviderName())->preUpdate($media);
        }

        $this->dm->persist($media);
        $this->dm->flush();

        if ($isNew) {
            $this->pool->getProvider($media->getProviderName())->postPersist($media);
        } else {
            $this->pool->getProvider($media->getProviderName())->postUpdate($media);
        }

        // just in case the pool alter the media
        $this->dm->persist($media);
        $this->dm->flush();
    }

    /**
     * Deletes a media
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function delete(MediaInterface $media)
    {
        $this->pool->preRemove($media);
        $this->dm->remove($media);
        $this->dm->flush();

        $this->pool->postRemove($media);
        $this->dm->flush();
    }
}