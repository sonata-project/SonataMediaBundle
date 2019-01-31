<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Exception\NoDriverException;

/**
 * @internal
 *
 * @author Andrey F. Mindubaev <covex.mobile@gmail.com>
 */
final class NoDriverManager implements ManagerInterface
{
    public function getClass()
    {
        throw new NoDriverException();
    }

    public function findAll()
    {
        throw new NoDriverException();
    }

    /**
     * @param int|null $limit
     * @param int|null $offset
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null): void
    {
        throw new NoDriverException();
    }

    public function findOneBy(array $criteria, array $orderBy = null): void
    {
        throw new NoDriverException();
    }

    /**
     * @param mixed $id
     */
    public function find($id): void
    {
        throw new NoDriverException();
    }

    public function create(): void
    {
        throw new NoDriverException();
    }

    /**
     * @param object $entity
     * @param bool   $andFlush
     */
    public function save($entity, $andFlush = true): void
    {
        throw new NoDriverException();
    }

    /**
     * @param object $entity
     * @param bool   $andFlush
     */
    public function delete($entity, $andFlush = true): void
    {
        throw new NoDriverException();
    }

    public function getTableName(): void
    {
        throw new NoDriverException();
    }

    public function getConnection(): void
    {
        throw new NoDriverException();
    }
}
