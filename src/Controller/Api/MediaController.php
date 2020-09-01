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

namespace Sonata\MediaBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Note: Media is plural, medium is singular (at least according to FOSRestBundle route generator).
 *
 * @final since sonata-project/media-bundle 3.21.0
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MediaController
{
    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var Pool
     */
    protected $mediaPool;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * Constructor.
     */
    public function __construct(MediaManagerInterface $mediaManager, Pool $mediaPool, FormFactoryInterface $formFactory)
    {
        $this->mediaManager = $mediaManager;
        $this->mediaPool = $mediaPool;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves a specific medium.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when medium is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Medium identifier
     *
     * @return MediaInterface
     */
    public function getMediumAction($id)
    {
        return $this->getMedium($id);
    }

    /**
     * Retrieves the list of media (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"={"sonata_api_read"}}
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Page for media list pagination")
     * @Rest\QueryParam(name="count", requirements="\d+", default="10", description="Number of medias by page")
     * @Rest\QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled medias filter")
     * @Rest\QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return PagerInterface
     */
    public function getMediaAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = [
            'enabled' => '',
        ];

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        foreach ($criteria as $key => $value) {
            if (null === $value) {
                unset($criteria[$key]);
            }
        }

        if (!$sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->mediaManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Returns medium urls for each format.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when medium is not found"
     *  }
     * )
     *
     * @param string $id Medium identifier
     *
     * @return array
     */
    public function getMediumFormatsAction($id)
    {
        $media = $this->getMedium($id);

        $formats = [MediaProviderInterface::FORMAT_REFERENCE];
        $formats = array_merge($formats, array_keys($this->mediaPool->getFormatNamesByContext($media->getContext())));

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $properties = [];
        foreach ($formats as $format) {
            $properties[$format]['url'] = $provider->generatePublicUrl($media, $format);
            $properties[$format]['properties'] = $provider->getHelperProperties($media, $format);
        }

        return $properties;
    }

    /**
     * Returns medium binary content for each format.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Medium identifier"},
     *      {"name"="format", "dataType"="string", "description"="Medium format"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when medium is not found"
     *  }
     * )
     *
     * @param string $id     Medium identifier
     * @param string $format Format
     *
     * @return Response
     */
    public function getMediumBinaryAction($id, $format, Request $request)
    {
        $media = $this->getMedium($id);

        $response = $this->mediaPool->getProvider($media->getProviderName())->getDownloadResponse($media, $format, $this->mediaPool->getDownloadMode($media));

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($request);
        }

        return $response;
    }

    /**
     * Deletes a medium.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when medium is successfully deleted",
     *      400="Returned when an error has occurred while deleting the medium",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @param string $id Medium identifier
     *
     * @throws NotFoundHttpException
     *
     * @return Rest\View
     */
    public function deleteMediumAction($id)
    {
        $medium = $this->getMedium($id);

        $this->mediaManager->delete($medium);

        return ['deleted' => true];
    }

    /**
     * Updates a medium.
     *
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while medium update",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @param string  $id      Medium identifier
     * @param Request $request Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    public function putMediumAction($id, Request $request)
    {
        $medium = $this->getMedium($id);

        try {
            $provider = $this->mediaPool->getProvider($medium->getProviderName());
        } catch (\RuntimeException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        } catch (\InvalidArgumentException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        return $this->handleWriteMedium($request, $medium, $provider);
    }

    /**
     * Adds a medium of given provider.
     *
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @ApiDoc(
     *  resource=true,
     *  input={"class"="sonata_media_api_form_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while medium creation",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @param string  $provider Media provider
     * @param Request $request  Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    public function postProviderMediumAction($provider, Request $request)
    {
        $medium = $this->mediaManager->create();
        $medium->setProviderName($provider);

        try {
            $mediaProvider = $this->mediaPool->getProvider($provider);
        } catch (\RuntimeException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        } catch (\InvalidArgumentException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        return $this->handleWriteMedium($request, $medium, $mediaProvider);
    }

    /**
     * Set Binary content for a specific media.
     *
     * @ApiDoc(
     *  input={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_write"}},
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string  $id      Medium identifier
     * @param Request $request Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    public function putMediumBinaryContentAction($id, Request $request)
    {
        $media = $this->getMedium($id);

        $media->setBinaryContent($request);

        $this->mediaManager->save($media);

        return $media;
    }

    /**
     * Retrieves media with identifier $id or throws an exception if not found.
     *
     * @param string $id Media identifier
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    protected function getMedium($id = null)
    {
        $media = $this->mediaManager->find($id);

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }

    /**
     * Write a medium, this method is used by both POST and PUT action methods.
     *
     * @return Rest\View|FormInterface
     */
    protected function handleWriteMedium(Request $request, MediaInterface $media, MediaProviderInterface $provider)
    {
        $form = $this->formFactory->createNamed(null, ApiMediaType::class, $media, [
            'provider_name' => $provider->getName(),
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $media = $form->getData();
            $this->mediaManager->save($media);

            $context = new Context();
            $context->setGroups(['sonata_api_read']);
            $context->enableMaxDepth();

            $view = FOSRestView::create($media);
            $view->setContext($context);

            return $view;
        }

        return $form;
    }
}
