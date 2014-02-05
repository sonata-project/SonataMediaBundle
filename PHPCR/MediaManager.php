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
    /**
     * @var \Doctrine\ODM\PHPCR\DocumentManager
     */
    protected $dm;

    /**
     * @param Pool         $pool
     * @param ModelManager $modelManager
     * @param string       $class
     */
    public function __construct(Pool $pool, ModelManager $modelManager, $class)
    {
        $this->dm = $modelManager->getDocumentManager();

        parent::__construct($pool, $class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When entity is an invalid object
     */
    public function save($entity, $andFlush = true)
    {
        /*
         * Warning: previous method signature was : save(MediaInterface $media, $context = null, $providerName = null)
         */

        if (!$entity instanceof MediaInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Entity remove must be instance of Sonata\\MediaBundle\\Model\\MediaInterface, %s given',
                is_object($entity)? get_class($entity) : gettype($entity)
            ));
        }

        // BC compatibility for $context parameter
        if ($andFlush && is_string($andFlush)) {
            $entity->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 == func_num_args()) {
            $entity->setProviderName(func_get_arg(2));
        }

        $this->dm->persist($entity);

        if ($andFlush && is_bool($andFlush)) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When entity is an invalid object
     */
    public function delete($entity, $andFlush = true)
    {
        if (!$entity instanceof MediaInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Entity remove must be instance of Sonata\\MediaBundle\\Model\\MediaInterface, %s given',
                is_object($entity)? get_class($entity) : gettype($entity)
            ));
        }

        $this->dm->remove($entity);

        if ($andFlush && is_bool($andFlush)) {
            $this->dm->flush();
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException Each call
     */
    public function getConnection()
    {
        throw new \LogicException('PHPCR does not use a database connection.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException Each call
     */
    public function getTableName()
    {
        throw new \LogicException('PHPCR does not use a reference name for a list of data.');
    }

    /**
     * {@inheritdoc}
     *
     * @return \Doctrine\ODM\PHPCR\DocumentRepository
     */
    protected function getRepository()
    {
        return $this->dm->getRepository($this->class);
    }
}
