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

use Sonata\BaseApplicationBundle\Admin\EntityAdmin as Admin;
use Sonata\BaseApplicationBundle\Form\FormMapper;
use Sonata\BaseApplicationBundle\Datagrid\DatagridMapper;
use Sonata\BaseApplicationBundle\Datagrid\ListMapper;

class GalleryAdmin extends Admin
{

    protected $list = array(
        'enabled',
        'name' => array('identifier' => true),
        'defaultFormat',
    );

    protected $form = array(
        'enabled',
        'name',
        'defaultFormat',
        'galleryHasMedias' => array('edit' => 'inline', 'inline' => 'table'),
        'code',
    );

    protected $filter = array(
        'name',
        'enabled',
    );

    public function configureFormFields(FormMapper $form)
    {

    }

    public function configureDatagridFilters(DatagridMapper $datagrid)
    {

    }

    public function configureListFields(ListMapper $list)
    {

    }

    public function preUpdate($object)
    {
        // fix weird bug with setter object not being call
        $object->setGalleryHasMedias($object->getGalleryHasMedias());
    }

    public function preInsert($object)
    {
        // fix weird bug with setter object not being call
        $object->setGalleryHasMedias($object->getGalleryHasMedias());
    }
}