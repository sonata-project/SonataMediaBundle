<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class GalleryAdmin extends Admin
{
    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('code')
            ->add('enabled')
            ->add('name')
            ->add('defaultFormat')
            ->add('galleryHasMedias', 'sonata_type_collection', array(), array(
                'edit' => 'inline',
                'inline' => 'table',
                'sortable'  => 'position'
            ))
        ;
    }

    public function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('enabled')
            ->addIdentifier('name')
            ->add('defaultFormat')
        ;
    }

    public function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('enabled')
        ;
    }

    public function preUpdate($object)
    {
        // fix weird bug with setter object not being call
        $object->setGalleryHasMedias($object->getGalleryHasMedias());
    }

    public function prePersist($object)
    {
        // fix weird bug with setter object not being call
        $object->setGalleryHasMedias($object->getGalleryHasMedias());
    }
}