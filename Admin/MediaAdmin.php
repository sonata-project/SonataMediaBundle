<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bundle\Sonata\MediaBundle\Admin;

use Bundle\Sonata\BaseApplicationBundle\Admin\Admin;

class MediaAdmin extends Admin
{

    protected $class = 'Application\MediaBundle\Entity\Media';

    protected $list_fields = array(
        'image'  => array('template' => 'MediaBundle:MediaAdmin:list_image.twig'),
        'custom' => array('template' => 'MediaBundle:MediaAdmin:list_custom.twig'),
        'enabled',
    );

    protected $form_fields = array(
        'enabled',
        'name',
        'description',
        'author_name',
        'copyright',
        'cdn_is_flushable'
    );

    protected $filter_fields = array(
        'name',
        'provider_reference',
        'enabled',
    );

    protected $base_route = 'sonata_media_admin';

    // don't know yet how to get this value
    protected $base_controller_name = 'MediaBundle:MediaAdmin';

}