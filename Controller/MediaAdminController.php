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
     * {@inheritdoc}
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        if (!$this->getRequest()->get('provider') && $this->getRequest()->isMethod('get')) {
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
     * {@inheritdoc}
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['media_pool']            = $this->container->get('sonata.media.pool');
        $parameters['persistent_parameters'] = $this->admin->getPersistentParameters();

        return parent::render($view, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        if ($listMode = $this->getRequest()->get('_list_mode')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();

        $filters = $this->getRequest()->get('filter');

        // set the default context
        if (!$filters || !array_key_exists('context', $filters)) {
            $context = $this->admin->getPersistentParameter('context',  $this->get('sonata.media.pool')->getDefaultContext());
        } else {
            $context = $filters['context']['value'];
        }

        $datagrid->setValue('context', null, $context);

        // retrieve the main category for the tree view
        $category = $this->container->get('sonata.classification.manager.category')->getRootCategory($context);

        if (!$filters) {
            $datagrid->setValue('category', null, $category->getId());
        }

        if ($this->getRequest()->get('category')) {
            $contextInCategory = $this->container->get('sonata.classification.manager.category')->findBy(array(
                'id'      => (int) $this->getRequest()->get('category'),
                'context' => $context
            ));

            if (!empty($contextInCategory)) {
                $datagrid->setValue('category', null, $this->getRequest()->get('category'));
            } else {
                $datagrid->setValue('category', null, $category->getId());
            }
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action'        => 'list',
            'form'          => $formView,
            'datagrid'      => $datagrid,
            'root_category' => $category,
            'csrf_token'    => $this->getCsrfToken('sonata.batch'),
        ));
    }
}
