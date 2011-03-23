<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MediaAdminController extends Controller
{

    public function viewAction($id)
    {
        $media = $this->get('sonata.media.entity_manager')->find('Application\Sonata\MediaBundle\Entity\Media', $id);

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }
        
        return $this->render('SonataMediaBundle:MediaAdmin:view.html.twig', array(
            'media'         => $media,
            'formats'       => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
            'format'        => $this->get('request')->get('format', 'reference'),
            'base_template' => $this->getBaseTemplate(),
            'admin'         => $this->admin,
            'side_menu'     => $this->getSideMenu('view'),
            'breadcrumbs'   => $this->getBreadcrumbs('view'),
        ));
    }

    public function createAction()
    {

        $parameters = $this->admin->getPersistentParameters();
        
        if (!$parameters['provider']) {
            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers'     => $this->get('sonata.media.pool')->getProvidersByContext($this->get('request')->get('context', 'default')),
                'configuration' => $this->admin,
                'base_template' => $this->getBaseTemplate(),
                'side_menu'     => false,
                'admin'         => $this->admin,
                'side_menu'     => $this->getSideMenu('create'),
                'breadcrumbs'   => $this->getBreadcrumbs('create'),
            ));
        }

        return parent::createAction();
    }
}