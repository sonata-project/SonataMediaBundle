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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Sonata\DatagridBundle\Pager\PagerInterface;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Note: Media is plural, medium is singular (at least according to FOSRestBundle route generator).
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
     *
     * @param MediaManagerInterface $mediaManager
     * @param Pool                  $mediaPool
     * @param FormFactoryInterface  $formFactory
     */
    public function __construct(MediaManagerInterface $mediaManager, Pool $mediaPool, FormFactoryInterface $formFactory)
    {
        $this->mediaManager = $mediaManager;
        $this->mediaPool = $mediaPool;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of medias (paginated).
     *
     * @Operation(
     *     tags={""},
     *     summary="Retrieves the list of medias (paginated).",
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page for media list pagination",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="count",
     *         in="query",
     *         description="Number of medias by page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Enabled/Disabled medias filter",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Order by array (key is field, value is direction)",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\DatagridBundle\Pager\PagerInterface",groups={"sonata_api_read"}))
     *     )
     * )
     *
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for media list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of medias by page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled medias filter")
     * @QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
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
     * Retrieves a specific media.
     *
     * @Operation(
     *     tags={""},
     *     summary="Retrieves a specific media.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when media is not found"
     *     )
     * )
     *
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return MediaInterface
     */
    public function getMediumAction($id)
    {
        return $this->getMedium($id);
    }

    /**
     * Returns media urls for each format.
     *
     * @Operation(
     *     tags={""},
     *     summary="Returns media urls for each format.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when media is not found"
     *     )
     * )
     *
     *
     * @param $id
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
     * Returns media binary content for each format.
     *
     * @Operation(
     *     tags={""},
     *     summary="Returns media binary content for each format.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="string",
     *          name="format",
     *          in="path",
     *          description="media format",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when media is not found"
     *     )
     * )
     *
     *
     * @param int     $id      The media id
     * @param string  $format  The format
     * @param Request $request
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
     * @Operation(
     *     tags={""},
     *     summary="Deletes a medium.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when medium is successfully deleted"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while deleting the medium"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find medium"
     *     )
     * )
     *
     *
     * @param int $id A medium identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function deleteMediumAction($id)
    {
        $medium = $this->getMedium($id);

        $this->mediaManager->delete($medium);

        return ['deleted' => true];
    }

    /**
     * Updates a medium
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @Operation(
     *     tags={""},
     *     summary="Updates a medium",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Media data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiMediaType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while medium update"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find medium"
     *     )
     * )
     *
     *
     * @param int     $id      A Medium identifier
     * @param Request $request A Symfony request
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
     * Adds a medium of given provider
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @Operation(
     *     tags={""},
     *     summary="Adds a medium of given provider",
     *     @SWG\Parameter(
     *          type="string",
     *          name="provider",
     *          in="path",
     *          description="provider id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Media data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiMediaType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while medium creation"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find medium"
     *     )
     * )
     *
     *
     * @Route(requirements={"provider"="[A-Za-z0-9.]*"})
     *
     * @param string  $provider A media provider
     * @param Request $request  A Symfony request
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
     * @Operation(
     *     tags={""},
     *     summary="Set Binary content for a specific media.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="binaryContent",
     *         in="body",
     *         description="Binary content of media",
     *         required=true,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when media is not found"
     *     )
     * )
     *
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     * @param Request $request A Symfony request
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
     * Retrieves media with id $id or throws an exception if not found.
     *
     * @param int $id
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    protected function getMedium($id = null)
    {
        $media = $this->mediaManager->findOneBy(['id' => $id]);

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }

    /**
     * Write a medium, this method is used by both POST and PUT action methods.
     *
     * @param Request                $request
     * @param MediaInterface         $media
     * @param MediaProviderInterface $provider
     *
     * @return View|FormInterface
     */
    protected function handleWriteMedium(Request $request, MediaInterface $media, MediaProviderInterface $provider)
    {
        $form = $this->formFactory->createNamed(null, 'sonata_media_api_form_media', $media, [
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
