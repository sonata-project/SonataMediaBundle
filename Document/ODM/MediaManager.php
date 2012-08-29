<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Sonata\MediaBundle\Document\ODM;

use Sonata\MediaBundle\Model\MediaManager as AbstractMediaManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Sonata\MediaBundle\Provider\Pool;

class MediaManager extends AbstractMediaManager
{
    protected $dm;
    protected $repository;
    protected $class;
    protected $pool;

    /**
     * @param \Sonata\MediaBundle\Provider\Pool     $pool
     * @param \Doctrine\ODM\MongoDB\DocumentManager $dm
     * @param string                                $class
     */
    public function __construct(Pool $pool, DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->pool = $pool;
        parent::__construct($pool, $class);
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
    public function save(MediaInterface $media, $context = null, $providerName = null)
    {
        if ($context) {
            $media->setContext($context);
        }

        if ($providerName) {
            $media->setProviderName($providerName);
        }

        $subscriber = new ProviderSubscriber($this->pool->getProvider($media->getProviderName()));
        $this->dm->getEventManager()->addEventSubscriber($subscriber);
        // just in case the pool alter the media
        $this->dm->persist($media);
        $this->dm->flush();
    }
    
    /**
     * {@inheritdoc}
     */
    public function delete(MediaInterface $media)
    {
        $subscriber = new ProviderSubscriber($this->pool->getProvider($media->getProviderName()));
        $this->dm->getEventManager()->addEventSubscriber($subscriber);
        $this->dm->remove($media);
        $this->dm->flush();
    }
}