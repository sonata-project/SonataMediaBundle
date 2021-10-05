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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Form\Type\ApiMediaType;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * NEXT_MAJOR: Remove this file.
 *
 * Note: Media is plural, medium is singular (at least according to FOSRestBundle route generator).
 *
 * @author Hugo Briand <briand@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.34, to be removed in 4.0.
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
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Retrieves a specific medium.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when medium is not found"
     *     )
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
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Retrieves the list of media (paginated).",
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
     *         description="Number of medias per page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Enables or disables the medias filter",
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
     *         @SWG\Schema(ref=@Model(type="Sonata\DatagridBundle\Pager\PagerInterface"))
     *     )
     * )
     *
     * @Rest\QueryParam(name="page", requirements="\d+", default="1", description="Page for media list pagination")
     * @Rest\QueryParam(name="count", requirements="\d+", default="10", description="Number of medias per page")
     * @Rest\QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enables or disables the medias filter")
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

        $criteria = array_filter($criteria, static function ($value): bool {
            return null !== $value;
        });

        if (null === $sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->mediaManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Returns medium urls for each format.
     *
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Returns medium urls for each format.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when medium is not found"
     *     )
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
            $properties[$format] = [
                'url' => $provider->generatePublicUrl($media, $format),
                'properties' => $provider->getHelperProperties($media, $format),
            ];
        }

        return $properties;
    }

    /**
     * Returns medium binary content for each format.
     *
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Returns medium binary content for each format.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when medium is not found"
     *     )
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
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Retrieves a specific medium.",
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
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Retrieves the list of media (paginated).",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media"))
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
        } catch (\RuntimeException | \InvalidArgumentException $ex) {
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
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Returns medium urls for each format.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media"))
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
        } catch (\RuntimeException | \InvalidArgumentException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        return $this->handleWriteMedium($request, $medium, $mediaProvider);
    }

    /**
     * Set Binary content for a media.
     *
     * @Operation(
     *     tags={"/api/media/media"},
     *     summary="Returns medium binary content for each format.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when media is not found"
     *     )
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
            throw new NotFoundHttpException(sprintf('Media not found for identifier %s.', var_export($id, true)));
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
        $form = $this->formFactory->createNamed('', ApiMediaType::class, $media, [
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
