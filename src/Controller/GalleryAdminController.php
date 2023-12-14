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

use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\MediaBundle\Model\GalleryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<GalleryInterface>
 */
final class GalleryAdminController extends CRUDController
{
    /**
     * return the Response object associated to the list action.
     */
    public function listAction(Request $request): Response
    {
        $this->assertObjectExists($request);

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $listMode = $request->query->get('_list_mode');
        if (\is_string($listMode)) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));

        $formView = $datagrid->getForm()->createView();

        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        if ($this->container->has('sonata.admin.admin_exporter')) {
            $exporter = $this->container->get('sonata.admin.admin_exporter');
            \assert($exporter instanceof AdminExporter);
            $exportFormats = $exporter->getAvailableFormats($this->admin);
        }

        return $this->render($this->admin->getTemplateRegistry()->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $exportFormats ?? $this->admin->getExportFormats(),
        ]);
    }
}
