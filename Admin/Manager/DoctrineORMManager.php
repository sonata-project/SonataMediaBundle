<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin\Manager;

use Doctrine\DBAL\DBALException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\DoctrineORMAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * this method overwrite the default AdminModelManager to call
 * the custom methods from the dedicated media manager.
 */
class DoctrineORMManager extends ModelManager
{
    /**
     * @var ManagerInterface
     */
    protected $manager;

    /**
     * @param RegistryInterface $registry
     * @param ManagerInterface  $manager
     */
    public function __construct(RegistryInterface $registry, $manager)
    {
        parent::__construct($registry);

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
     * {@inheritdoc}
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
