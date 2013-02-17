<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin\PHPCR;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\MediaBundle\Admin\GalleryAdmin as BaseGalleryAdmin;

class GalleryAdmin extends BaseGalleryAdmin
{
    /**
     * Path to the root node of gallery documents.
     *
     * @var string
     */
    protected $root;

    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * {@inheritdoc}
     */
    public function createQuery($context = 'list')
    {
        $query = $this->getModelManager()->createQuery($this->getClass(), '', $this->root);

        foreach ($this->extensions as $extension) {
            $extension->configureQuery($this, $query, $context);
        }

        return $query;
    }

    public function id($object)
    {
        return $this->getUrlsafeIdentifier($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        // TODO disabled filter due to no attached service for filter types: string, checkbox
//        $datagridMapper
//            ->add('name')
//            ->add('enabled')
//            ->add('context')
        ;
    }
}