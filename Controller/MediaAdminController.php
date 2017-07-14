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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Form\Type\MultiUploadType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\MultiUploadInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
     * @param Request $request
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException|\LogicException
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response
     */
    final public function multiUploadAction(Request $request)
    {
        $this->admin->checkAccess('create');

        $defaultContext = $this->get('sonata.media.pool')->getDefaultContext();
        $context = $request->get('context', $defaultContext);

        $availableProviders = $this->getProvidersForMultiUploadByContext($context);
        $providerName = $this->getRequest()->get('provider');
        $provider = isset($availableProviders[$providerName]) ? $this->get($providerName) : null;

        if (!$provider) {
            return $this->render('SonataMediaBundle:MediaAdmin:select_provider.html.twig', array(
                'providers' => $availableProviders,
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'action' => 'multi_upload',
            ));
        } elseif (!$provider instanceof MultiUploadInterface) {
            throw new \LogicException(sprintf('The provider %s does not implement MultiUploadInterface', $providerName));
        }

        return $this->render(
            $provider->getTemplate('multi_upload_input'),
            array(
                'action' => 'multi_upload',
                'form' => $this->createMultiUploadForm($provider, $context)->createView(),
            )
        );
    }

    /**
     * @param Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    final public function multiUploadSubmitAction(Request $request)
    {
        $this->admin->checkAccess('create');

        $context = $request->get('context');
        $availableProviders = $this->getProvidersForMultiUploadByContext($context);
        $providerName = $this->getRequest()->get('provider');

        if (!isset($availableProviders[$providerName])) {
            throw new BadRequestHttpException(sprintf('Provider %s does not exist in context %s or does not implement MultiUploadInterface', $providerName, $context));
        }

        /** @var $provider MediaProviderInterface */
        $provider = $this->get($providerName);

        $form = $this->createMultiUploadForm($provider);
        $form->handleRequest($request);

        $files = array();
        if ($form->isSubmitted()) {
            /** @var $media MediaInterface */
            $media = $form->getData();

            /** @var $provider MediaProviderInterface */
            $provider = $this->get($media->getProviderName());
            try {
                $mediaManager = $this->get('sonata.media.manager.media');
                $mediaManager->save($media);

                $files[] = array(
                    'name' => $media->getName(),
                    'editUrl' => $this->admin->generateUrl('edit', array('id' => $media->getId())),
                    'thumbnailUrl' => $provider->getCdnPath($provider->getReferenceImage($media), true),
                );
            } catch (\Exception $e) {
                $this->get('logger')->error('Could not save media', array('exception' => $e, 'media' => $media));
                $uploadedFile = $media->getBinaryContent();
                $files[] = array(
                    'name' => $uploadedFile instanceof UploadedFile ? $uploadedFile->getClientOriginalName() : '',
                    'error' => $e->getMessage(),
                );
            }
        }

        return new JsonResponse(array('files' => $files));
    }

    /**
     * @param string $context
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface[]
     */
    private function getProvidersForMultiUploadByContext($context)
    {
        $filteredProviders = array();
        $pool = $this->get('sonata.media.pool');
        foreach ($pool->getProvidersByContext($context) as $provider) {
            if ($provider instanceof MultiUploadInterface) {
                $filteredProviders[$provider->getName()] = $provider;
            }
        }

        return $filteredProviders;
    }

    /**
     * @param MediaProviderInterface $provider
     * @param string                 $context
     *
     * @return mixed
     */
    private function createMultiUploadForm(MediaProviderInterface $provider, $context = 'default')
    {
        $formContractor = $this->admin->getFormContractor();

        /** @var $formFactory FormFactory */
        $formFactory = $formContractor->getFormFactory();
        $mediaManager = $this->get('sonata.media.manager.media');
        $mediaClass = $mediaManager->getClass();

        $formBuilder = $formFactory->createBuilder(
            new MultiUploadType($mediaClass),
            null,
            array(
                'provider' => $provider->getName(),
                'context' => $context,
                'action' => $this->admin->generateUrl(
                    'multi_upload_submit',
                    array(
                        'provider' => $provider->getName(),
                        'context' => $context,
                    )
                ),
            )
        );

        $formMapper = new FormMapper($formContractor, $formBuilder, $this->admin);
        $provider->configureMultiUpload($formMapper);

        return $formMapper->getFormBuilder()->getForm();
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
