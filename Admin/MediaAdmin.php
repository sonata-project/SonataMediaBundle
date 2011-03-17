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

class MediaAdmin extends Admin
{
    protected $pool = null;
    
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

    public function __construct($class, $baseControllerName, $pool)
    {
        parent::__construct($class, $baseControllerName);

        $this->pool = $pool;
    }

    public function configureFormFields(FormMapper $formMapper)
    {
        $media = $formMapper->getForm()->getData();

        $provider = $this->pool->getProvider($media->getProviderName());

        if($media->getId() > 0) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
    }

    public function configureDatagridFilters(DatagridMapper $datagrid)
    {

    }

    public function configureListFields(ListMapper $list)
    {

    }
}