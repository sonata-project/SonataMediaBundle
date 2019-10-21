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

namespace Sonata\MediaBundle\Admin\ODM;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaAdmin extends Admin
{
    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('providerReference')
            ->add('enabled')
            ->add('context')
        ;
    }
}
