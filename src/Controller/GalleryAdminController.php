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

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GalleryAdminController extends CRUDController
{
    /**
     * return the Response object associated to the list action.
     */
    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        if ($listMode = $request->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));

        $formView = $datagrid->getForm()->createView();

        $twig = $this->get('twig');

        // set the theme for the current Admin Form
        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());

        return $this->renderWithExtraParams($this->admin->getTemplateRegistry()->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ]);
    }
}
