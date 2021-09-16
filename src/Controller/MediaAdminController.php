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
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<\Sonata\MediaBundle\Model\MediaInterface>
 */
final class MediaAdminController extends CRUDController
{
    public static function getSubscribedServices(): array
    {
        return [
            'sonata.media.pool' => Pool::class,
            'sonata.media.manager.category' => '?'.CategoryManagerInterface::class,
            'sonata.media.manager.context' => '?'.ContextManagerInterface::class,
        ] + parent::getSubscribedServices();
    }

    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        if (null === $request->get('provider') && $request->isMethod('get')) {
            $pool = $this->container->get('sonata.media.pool');
            \assert($pool instanceof Pool);

            return $this->renderWithExtraParams('@SonataMedia/MediaAdmin/select_provider.html.twig', [
                'providers' => $pool->getProvidersByContext(
                    $request->get('context', $pool->getDefaultContext())
                ),
                'action' => 'create',
            ]);
        }

        return parent::createAction($request);
    }

    public function listAction(Request $request): Response
    {
        $this->assertObjectExists($request);

        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setListMode($request->get('_list_mode', 'mosaic'));

        $datagrid = $this->admin->getDatagrid();

        $filters = $request->get('filter', []);

        // set the default context
        if ([] === $filters || !\array_key_exists('context', $filters)) {
            $pool = $this->container->get('sonata.media.pool');
            \assert($pool instanceof Pool);

            $context = $this->admin->getPersistentParameter('context') ?? $pool->getDefaultContext();
        } else {
            $context = $filters['context']['value'];
        }

        $datagrid->setValue('context', null, $context);

        $rootCategory = null;
        if (
            $this->container->has('sonata.media.manager.category') &&
            $this->container->has('sonata.media.manager.context')
        ) {
            $categoryManager = $this->container->get('sonata.media.manager.category');
            \assert($categoryManager instanceof CategoryManagerInterface);
            $contextManager = $this->container->get('sonata.media.manager.context');
            \assert($contextManager instanceof ContextManagerInterface);

            $rootCategories = $categoryManager->getRootCategoriesForContext($contextManager->find($context));

            if ([] !== $rootCategories) {
                $rootCategory = current($rootCategories);
            }

            if (null !== $rootCategory && [] === $filters) {
                $datagrid->setValue('category', null, $rootCategory->getId());
            }

            if (null !== $request->get('category')) {
                $category = $categoryManager->findOneBy([
                    'id' => (int) $request->get('category'),
                    'context' => $context,
                ]);

                if (null !== $category) {
                    $datagrid->setValue('category', null, $category->getId());
                } elseif (null !== $rootCategory) {
                    $datagrid->setValue('category', null, $rootCategory->getId());
                }
            }
        }

        $formView = $datagrid->getForm()->createView();

        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        if ($this->container->has('sonata.admin.admin_exporter')) {
            $exporter = $this->container->get('sonata.admin.admin_exporter');
            \assert($exporter instanceof AdminExporter);
            $exportFormats = $exporter->getAvailableFormats($this->admin);
        }

        return $this->renderWithExtraParams($this->admin->getTemplateRegistry()->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'root_category' => $rootCategory,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $exportFormats ?? $this->admin->getExportFormats(),
        ]);
    }
}
