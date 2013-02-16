<?php

namespace Sonata\MediaBundle\Admin\Manager;

use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * this method overwrite the default AdminModelManager to call
 * the custom methods from the dedicated media manager
 */
class DoctrineORMManager extends ModelManager
{
    protected $manager;

    /**
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     * @param mixed                                      $manager
     */
    public function __construct(RegistryInterface $doctrine, $manager)
    {
        parent::__construct($doctrine);

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
     * Deletes a set of $class identified by the provided $idx array
     *
     * @param string                                           $class
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $queryProxy
     *
     * @return void
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy)
    {
        try {
            foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
                $this->delete($object[0]);
            }
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }
}
