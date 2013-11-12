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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaAdminController extends Controller
{
    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException|\Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param string $id
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function showAction($id = null)
    {
        if (false === $this->admin->isGranted('VIEW')) {
            throw new AccessDeniedException();
        }

        $media = $this->admin->getObject($id);

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->render('SonataMediaBundle:MediaAdmin:show.html.twig', array(
            'media'         => $media,
            'formats'       => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
            'format'        => $this->get('request')->get('format', 'reference'),
            'base_template' => $this->getBaseTemplate(),
            'admin'         => $this->admin,
            'security'      => $this->get('sonata.media.pool')->getDownloadSecurity($media),
            'action'        => 'view',
            'pixlr'         => $this->container->has('sonata.media.extra.pixlr') ? $this->container->get('sonata.media.extra.pixlr') : false,
        ));
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $parameters = $this->admin->getPersistentParameters();

        if (!$parameters['provider']) {
            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers'     => $this->get('sonata.media.pool')->getProvidersByContext($this->get('request')->get('context', $this->get('sonata.media.pool')->getDefaultContext())),
                'base_template' => $this->getBaseTemplate(),
                'admin'         => $this->admin,
                'action'        => 'create'
            ));
        }

        return parent::createAction();
    }

    /**
     * @param string                                          $view
     * @param array                                           $parameters
     * @param null|\Symfony\Component\HttpFoundation\Response $response
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['media_pool']            = $this->container->get('sonata.media.pool');
        $parameters['persistent_parameters'] = $this->admin->getPersistentParameters();

        return parent::render($view, $parameters);
    }

    /**
     * return the Response object associated to the list action
     *
     * @return Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action'     => 'list',
            'form'       => $formView,
            'datagrid'   => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ));
    }
}
