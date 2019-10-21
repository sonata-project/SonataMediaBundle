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
use Sonata\MediaBundle\Model\MediaManagerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaManager extends BaseEntityManager implements MediaManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function save($media, $andFlush = true)
    {
        /*
         * Warning: previous method signature was : save(MediaInterface $media, $context = null, $providerName = null)
         */

        // BC compatibility for $context parameter
        if ($andFlush && \is_string($andFlush)) {
            $media->setContext($andFlush);
        }

        // BC compatibility for $providerName parameter
        if (3 === \func_num_args()) {
            $media->setProviderName(func_get_arg(2));
        }

        if (\is_bool($andFlush)) {
            parent::save($media, $andFlush);
        } else {
            // BC compatibility with previous signature
            parent::save($media, true);
        }
    }

    /**
     * {@inheritdoc}
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
