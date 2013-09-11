<?php

namespace Sonata\MediaBundle\Admin\Manager;

use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * this method overwrite the default AdminModelManager to call
 * the custom methods from the dedicated media manager
 */
class DoctrineMongoDBManager extends ModelManager
{
    protected $manager;

    /**
     * @param mixed $entityManager
     * @param mixed $manager
     */
    public function __construct($entityManager, $manager)
    {
        parent::__construct($entityManager);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        $this->manager->save($object);
    }

    /**
     * {@inheritdoc}
     */
    public function update($object)
    {
        $this->manager->save($object);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        $this->manager->delete($object);
    }

    /**
     * {@inheritdoc}
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
            $this->delete($object);
        }
    }
}
