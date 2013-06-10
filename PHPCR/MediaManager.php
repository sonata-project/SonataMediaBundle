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
use Sonata\MediaBundle\Provider\Pool;
use Sonata\DoctrinePHPCRAdminBundle\Model\ModelManager;

class MediaManager extends AbstractMediaManager
{
    protected $modelManager;
    protected $repository;
    protected $class;

    /**
     * @param \Sonata\MediaBundle\Provider\Pool               $pool
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface $modelManager
     * @param $class
     */
    public function __construct(Pool $pool, ModelManager $modelManager, $class)
    {
        $this->modelManager = $modelManager;

        parent::__construct($pool, $class);
    }

    /**
     * Filter criteria for an identifier, phpcr-odm uses absolute paths and needs an identifier starting with a forward slash
     *
     * @param  array $criteria
     * @return array
     */
    protected function filterCriteria(array $criteria)
    {
        $identifier = $this->modelManager->getModelIdentifier($this->class);

        if (isset($criteria[$identifier])) {
            $criteria[$identifier] = $this->modelManager->getBackendId($criteria[$identifier]);
        }

        return $criteria;
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     *
     * @return Media
     */
    public function findOneBy(array $criteria)
    {
        $identifier = $this->modelManager->getModelIdentifier($this->class);

        if (count($criteria) === 1 && isset($criteria[$identifier])) {
            return $this->modelManager->find($this->class, $criteria[$identifier]);
        }

        return $this->modelManager->findOneBy($this->class, $this->filterCriteria($criteria));
    }

    /**
     * Finds one media by the given criteria
     *
     * @param array $criteria
     *
     * @return Media
     */
    public function findBy(array $criteria)
    {
        $identifier = $this->modelManager->getModelIdentifier($this->class);

        if (count($criteria) === 1 && isset($criteria[$identifier])) {
            return $this->modelManager->find($this->class, $criteria[$identifier]);
        }

        return $this->modelManager->findBy($this->class, $this->filterCriteria($criteria));
    }

    /**
     * Updates a media
     *
     * @param  \Sonata\MediaBundle\Model\MediaInterface $media
     * @param  string                                   $context
     * @param  string                                   $providerName
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
        $this->modelManager->update($media);
    }

    /**
     * Deletes a media
     *
     * @param  \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function delete(MediaInterface $media)
    {
        $this->modelManager->delete($media);
    }
}
