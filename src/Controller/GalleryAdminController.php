<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class GalleryAdminController extends Controller
{
    /**
     * @param string   $view
     * @param Response $response
     *
     * @return Response
     */
    public function render($view, array $parameters = [], Response $response = null)
    {
        $parameters['media_pool'] = $this->get('sonata.media.pool');
        $parameters['persistent_parameters'] = $this->admin->getPersistentParameters();

        return parent::renderWithExtraParams($view, $parameters, $response);
    }

    /**
     * return the Response object associated to the list action.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request = null)
    {
        $this->admin->checkAccess('list');

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     */
    private function setFormTheme(FormView $formView, array $theme)
    {
        $twig = $this->get('twig');

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }
}
