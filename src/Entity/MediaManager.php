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

namespace Sonata\MediaBundle\Entity;

use Sonata\DatagridBundle\Pager\Doctrine\Pager;
use Sonata\DatagridBundle\ProxyQuery\Doctrine\ProxyQuery;
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @phpstan-template T of MediaInterface
 * @phpstan-extends BaseEntityManager<T>
 * @phpstan-implements MediaManagerInterface<T>
 */
class MediaManager extends BaseEntityManager implements MediaManagerInterface
{
    /**
     * Warning: previous method signature was:
     * `save(MediaInterface $media, ?string $context = null, ?string $providerName = null)`.
     */
    public function save($entity, $andFlush = true)
    {
        // BC compatibility for argument 2.
        if ($andFlush && \is_string($andFlush)) {
            $entity->setContext($andFlush);
        }

        // BC compatibility for argument 3.
        if (3 === \func_num_args()) {
            $entity->setProviderName(func_get_arg(2));
        }

        if (\is_bool($andFlush)) {
            parent::save($entity, $andFlush);
        } else {
            // BC compatibility with previous signature.
            parent::save($entity, true);
        }
    }

    /**
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated since sonata-project/media-bundle 3.34, to be removed in 4.0.
     */
    public function getPager(array $criteria, $page, $limit = 10, array $sort = [])
    {
        $query = $this->getRepository()
            ->createQueryBuilder('m')
            ->select('m');

        $fields = $this->getEntityManager()->getClassMetadata($this->class)->getFieldNames();
        foreach ($sort as $field => $direction) {
            if (!\in_array($field, $fields, true)) {
                throw new \RuntimeException(sprintf("Invalid sort field '%s' in '%s' class", $field, $this->class));
            }
        }

        foreach ($sort as $field => $direction) {
            $query->orderBy(sprintf('m.%s', $field), strtoupper($direction));
        }

        $parameters = [];

        if (isset($criteria['enabled'])) {
            $query->andWhere('m.enabled = :enabled');
            $parameters['enabled'] = $criteria['enabled'];
        }

        $query->setParameters($parameters);

        $pager = new Pager();
        $pager->setMaxPerPage($limit);
        $pager->setQuery(new ProxyQuery($query));
        $pager->setPage($page);
        $pager->init();

        return $pager;
    }
}
