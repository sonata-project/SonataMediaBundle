<?php

namespace Sonata\MediaBundle\Admin\Manager;

use Doctrine\DBAL\DBALException;
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
        try {
            $this->manager->save($object);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update($object)
    {
        try {
            $this->manager->save($object);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($object)
    {
        try {
            $this->manager->delete($object);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
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
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }
}
