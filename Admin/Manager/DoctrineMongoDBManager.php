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

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\DoctrineMongoDBAdminBundle\Model\ModelManager;
use Symfony\Bridge\Doctrine\ManagerRegistry;

/**
 * this method overwrite the default AdminModelManager to call
 * the custom methods from the dedicated media manager.
 */
class DoctrineMongoDBManager extends ModelManager
{
    /**
     * @var ManagerInterfacece
     */
    protected $manager;

    /**
     * @param ManagerRegistry  $managerRegistry
     * @param ManagerInterface $manager
     */
    public function __construct($managerRegistry, $manager)
    {
        parent::__construct($managerRegistry);

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
