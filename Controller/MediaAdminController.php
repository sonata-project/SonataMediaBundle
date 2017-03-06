<?php

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
use Sonata\MediaBundle\Form\Type\MultiUploadType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MediaAdminController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        if (!$request->get('provider') && $request->isMethod('get')) {
            $pool = $this->get('sonata.media.pool');

            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers' => $pool->getProvidersByContext(
                    $request->get('context', $pool->getDefaultContext())
                ),
                'action' => 'create',
            ));
        }

        return parent::createAction();
    }

    /**
     * {@inheritdoc}
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $parameters['media_pool'] = $this->get('sonata.media.pool');
        $parameters['persistent_parameters'] = $this->admin->getPersistentParameters();

        return parent::render($view, $parameters, $response);
    }

    /**
     * {@inheritdoc}
     */
    public function listAction(Request $request = null)
    {
        $this->admin->checkAccess('list');

        if ($listMode = $request->get('_list_mode', 'mosaic')) {
            $this->admin->setListMode($listMode);
        }

        $datagrid = $this->admin->getDatagrid();

        $filters = $request->get('filter');

        // set the default context
        if (!$filters || !array_key_exists('context', $filters)) {
            $context = $this->admin->getPersistentParameter('context', $this->get('sonata.media.pool')->getDefaultContext());
        } else {
            $context = $filters['context']['value'];
        }

        $datagrid->setValue('context', null, $context);

        $rootCategory = null;
        if ($this->has('sonata.media.manager.category')) {
            $rootCategory = $this->get('sonata.media.manager.category')->getRootCategory($context);
        }

        if (null !== $rootCategory && !$filters) {
            $datagrid->setValue('category', null, $rootCategory->getId());
        }
        if ($this->has('sonata.media.manager.category') && $request->get('category')) {
            $category = $this->get('sonata.media.manager.category')->findOneBy(array(
                'id' => (int) $request->get('category'),
                'context' => $context,
            ));

            if (!empty($category)) {
                $datagrid->setValue('category', null, $category->getId());
            } else {
                $datagrid->setValue('category', null, $rootCategory->getId());
            }
        }

        $formView = $datagrid->getForm()->createView();

        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        return $this->render($this->admin->getTemplate('list'), array(
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'root_category' => $rootCategory,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
        ));
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function multiUploadAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $parameters = $this->admin->getPersistentParameters();

        $provider = $this->getRequest()->get('provider');

        if (!$provider) {
            $providers = $this->get('sonata.media.pool')->getProvidersByContext($this->get('request')->get('context', $this->get('sonata.media.pool')->getDefaultContext()));

            $filteredProviders = array();
            foreach ($providers as $key => $provider) {
                if ($provider instanceof FileProvider) {
                    $filteredProviders[] = $provider;
                }
            }

            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers' => $filteredProviders,
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'multi_upload',
            ));
        }

        $form = $this->createForm(new MultiUploadType(), null, array('provider' => $provider, 'context' => $parameters['context']));

        return $this->render('SonataMediaBundle:MediaAdmin:multi_upload.html.twig', array(
            'action' => 'multi_upload',
            'multi_form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function multiUploadAjaxAction(Request $request)
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $form = $this->createForm(new MultiUploadType());
        $form->handleRequest($request);

        $logger = $this->get('logger');

        $files = array();
        if ($form->isValid()) {
            $data = $form->getData();

            $uploadedFiles = $data['files'];
            $mediaPool = $this->get('sonata.media.pool');
            /** @var $uploadedFile UploadedFile */
            foreach ($uploadedFiles as $uploadedFile) {
                if ($uploadedFile->isValid()) {
                    $this->admin->setRequest($request);
                    /** @var $media MediaInterface */
                    $media = $this->admin->getNewInstance();
                    $media->setProviderName($data['provider']);
                    $media->setContext($data['context']);
                    $media->setBinaryContent($uploadedFile);

                    try {
                        $media = $this->admin->update($media);
                        $provider = $mediaPool->getProvider($data['provider']);

                        $files[] = array(
                            'name' => $media->getName(),
                            'editUrl' => $this->admin->generateUrl('edit', array('id' => $media->getId())),
                            'thumbnailUrl' => $provider->getCdnPath($provider->getReferenceImage($media), true),
                        );
                    } catch (\Exception $e) {
                        $logger->error('Could not create Media', array('exception' => $e));
                        $files[] = array(
                            'name' => $uploadedFile->getClientOriginalName(),
                            'error' => $e->getMessage(),
                        );
                    }
                }
            }
        }

        return new JsonResponse(
            array(
                'files' => $files,
            )
        );
    }

    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     *
     * @param FormView $formView
     * @param string   $theme
     */
    private function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists('Symfony\Bridge\Twig\AppVariable', 'getToken')) {
            $twig->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')
                ->renderer->setTheme($formView, $theme);

            return;
        }
        $twig->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')->setTheme($formView, $theme);
    }
}
