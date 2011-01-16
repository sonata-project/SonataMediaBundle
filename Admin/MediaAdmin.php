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

use Bundle\Sonata\BaseApplicationBundle\Admin\EntityAdmin as Admin;

class MediaAdmin extends Admin
{

    protected $class = 'Application\Sonata\MediaBundle\Entity\Media';

    protected $listFields = array(
        'image'  => array('template' => 'SonataMediaBundle:MediaAdmin:list_image.twig.html'),
        'custom' => array('template' => 'SonataMediaBundle:MediaAdmin:list_custom.twig.html'),
        'enabled',
    );

    protected $formFields = array(
        'enabled',
        'name',
        'description',
        'author_name',
        'copyright',
        'cdn_is_flushable'
    );

    protected $filterFields = array(
        'name',
        'provider_reference',
        'enabled',
    );

    // don't know yet how to get this value
    protected $baseControllerName = 'SonataMediaBundle:MediaAdmin';

}