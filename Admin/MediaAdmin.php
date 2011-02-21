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