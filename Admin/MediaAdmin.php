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

use Sonata\AdminBundle\Admin\EntityAdmin as Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class MediaAdmin extends Admin
{
    protected $list = array(
        'image'  => array('template' => 'SonataMediaBundle:MediaAdmin:list_image.html.twig', 'type' => 'string'),
        'custom' => array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.html.twig', 'type' => 'string'),
        'enabled',
    );

    protected $form = array(
        'enabled',
        'name',
        'description',
        'authorName',
        'copyright',
        'cdnIsFlushable'
    );

    protected $filter = array(
        'name',
        'providerReference',
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
}