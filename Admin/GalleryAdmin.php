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

    protected $list = array(
        'enabled',
        'name' => array('identifier' => true),
        'defaultFormat',
    );

    protected $form = array(
        'code',
        'enabled',
        'name',
        'defaultFormat',
        'galleryHasMedias' => array(
            'edit' => 'inline',
            'inline' => 'table',
            'sortable' => 'position'
        ),
    );

    protected $filter = array(
        'name',
        'enabled'
    );

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