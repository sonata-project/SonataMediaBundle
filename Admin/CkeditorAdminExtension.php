<?php

/*
 * This file is part of the CKEditorSonataMediaBundle package.
 *
 * (c) La Coopérative des Tilleuls <contact@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Adds browser and upload routes to the Admin
 *
 * @author Kévin Dunglas <kevin@les-tilleuls.coop>
 */
class CkeditorAdminExtension extends AdminExtension
{
    /**
     * {@inheritDoc}
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add('ckeditor_browser', 'ckeditor_browser');
        $collection->add('ckeditor_upload', 'ckeditor_upload');
    }
}
