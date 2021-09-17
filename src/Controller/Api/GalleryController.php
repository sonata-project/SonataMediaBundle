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

use Doctrine\Common\Collections\Collection;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Form\Type\ApiGalleryItemType;
use Sonata\MediaBundle\Form\Type\ApiGalleryType;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * NEXT_MAJOR: Remove this file.
 *
 * @author Hugo Briand <briand@ekino.com>
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class GalleryController
{
    /**
     * @var GalleryManagerInterface
     */
    private $galleryManager;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(GalleryManagerInterface $galleryManager, MediaManagerInterface $mediaManager, FormFactoryInterface $formFactory)
    {
        $this->galleryManager = $galleryManager;
        $this->mediaManager = $mediaManager;
        $this->formFactory = $formFactory;
    }

    /**
     * Retrieves the list of galleries (paginated).
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves the list of galleries (paginated).",
     *     @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page for gallery list pagination",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="count",
     *         in="query",
     *         description="Number of galleries by page",
     *         required=false,
     *         type="string"
     *     ),
     *     @SWG\Parameter(
     *         name="enabled",
     *         in="query",
     *         description="Enables or disables galleries filter",
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
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="Page for gallery list pagination"
     * )
     * @Rest\QueryParam(
     *     name="count",
     *     requirements="\d+",
     *     default="10",
     *     description="Number of galleries per page"
     * )
     * @Rest\QueryParam(
     *     name="enabled",
     *     requirements="0|1",
     *     nullable=true,
     *     strict=true,
     *     description="Enables or disables the galleries filter"
     * )
     * @Rest\QueryParam(
     *     name="orderBy",
     *     map=true,
     *     requirements="ASC|DESC",
     *     nullable=true,
     *     strict=true,
     *     description="Order by array (key is field, value is direction)"
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @return PagerInterface<GalleryInterface>
     */
    public function getGalleriesAction(ParamFetcherInterface $paramFetcher): PagerInterface
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

        if (null === $sort) {
            $sort = [];
        } elseif (!\is_array($sort)) {
            $sort = [$sort => 'asc'];
        }

        return $this->galleryManager->getPager($criteria, (int) $page, (int) $limit, $sort);
    }

    /**
     * Retrieves a specific gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves a specific gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="sonata_media_api_form_gallery"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param int|string $id Gallery identifier
     */
    public function getGalleryAction($id): GalleryInterface
    {
        return $this->getGallery($id);
    }

    /**
     * Retrieves the medias of specified gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves the medias of specified gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param int|string $id Gallery identifier
     *
     * @return MediaInterface[]
     */
    public function getGalleryMediasAction($id): array
    {
        $galleryItems = $this->getGallery($id)->getGalleryItems();

        $media = [];
        foreach ($galleryItems as $galleryItem) {
            $medium = $galleryItem->getMedia();
            \assert(null !== $medium);

            $media[] = $medium;
        }

        return $media;
    }

    /**
     * Retrieves the gallery items of specified gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves the list of galleries (paginated).",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\GalleryItem"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param int|string $id Gallery identifier
     *
     * @return Collection<int, GalleryItemInterface>
     */
    public function getGalleryGalleryItemsAction($id): Collection
    {
        return $this->getGallery($id)->getGalleryItems();
    }

    /**
     * Adds a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Adds a gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="sonata_media_api_form_gallery"))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery creation"
     *     )
     * )
     *
     * @throws NotFoundHttpException
     *
     * @return FOSRestView|FormInterface
     */
    public function postGalleryAction(Request $request): object
    {
        return $this->handleWriteGallery($request);
    }

    /**
     * Updates a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Updates a gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="sonata_media_api_form_gallery"))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery creation"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find gallery"
     *     )
     * )
     *
     * @param int|string $id Gallery identifier
     *
     * @throws NotFoundHttpException
     *
     * @return FOSRestView|FormInterface
     */
    public function putGalleryAction($id, Request $request): object
    {
        return $this->handleWriteGallery($request, $id);
    }

    /**
     * Adds a medium to a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves a specific gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="sonata_media_api_form_gallery"))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery/media attachment"
     *     )
     * )
     *
     * @param int|string $galleryId Gallery identifier
     * @param int|string $mediaId   Medium identifier
     *
     * @throws NotFoundHttpException
     *
     * @return FOSRestView|FormInterface
     */
    public function postGalleryMediaGalleryItemAction($galleryId, $mediaId, Request $request): object
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);
        $galleryItemExists = $gallery->getGalleryItems()->exists(static function ($key, GalleryItemInterface $element) use ($media): bool {
            $elementMedia = $element->getMedia();

            return null !== $elementMedia && $elementMedia->getId() === $media->getId();
        });

        if ($galleryItemExists) {
            return FOSRestView::create([
                'error' => sprintf('Gallery "%s" already has media "%s"', $galleryId, $mediaId),
            ], 400);
        }

        return $this->handleWriteGalleryItem($gallery, $media, null, $request);
    }

    /**
     * Updates a medium to a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves the medias of specified gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="sonata_media_api_form_gallery"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when an error if medium cannot be found in gallery"
     *     )
     * )
     *
     * @param int|string $galleryId Gallery identifier
     * @param int|string $mediaId   Medium identifier
     *
     * @throws NotFoundHttpException
     *
     * @return FOSRestView|FormInterface
     */
    public function putGalleryMediaGalleryItemAction($galleryId, $mediaId, Request $request): object
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryItems() as $galleryItem) {
            $galleryItemMedia = $galleryItem->getMedia();

            if (null !== $galleryItemMedia && $galleryItemMedia->getId() === $media->getId()) {
                return $this->handleWriteGalleryItem($gallery, $media, $galleryItem, $request);
            }
        }

        throw new NotFoundHttpException(sprintf('Gallery "%s" does not have media "%s"', $galleryId, $mediaId));
    }

    /**
     * Deletes a medium association to a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Retrieves the list of galleries (paginated).",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when medium is successfully deleted from gallery"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while medium deletion of gallery"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find gallery or media"
     *     )
     * )
     *
     * @param int|string $galleryId Gallery identifier
     * @param int|string $mediaId   Media identifier
     *
     * @throws NotFoundHttpException
     */
    public function deleteGalleryMediaGalleryItemAction($galleryId, $mediaId): FOSRestView
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryItems() as $key => $galleryItem) {
            $galleryItemMedia = $galleryItem->getMedia();

            if (null !== $galleryItemMedia && $galleryItemMedia->getId() === $media->getId()) {
                $gallery->getGalleryItems()->remove($key);
                $this->galleryManager->save($gallery);

                return FOSRestView::create(['deleted' => true]);
            }
        }

        return FOSRestView::create([
            'error' => sprintf('Gallery "%s" does not have media "%s" associated', $galleryId, $mediaId),
        ], 400);
    }

    /**
     * Deletes a gallery.
     *
     * @Operation(
     *     tags={"/api/media/galleries"},
     *     summary="Deletes a gallery.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when gallery is successfully deleted"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery deletion"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find gallery"
     *     )
     * )
     *
     * @param int|string $id Gallery identifier
     *
     * @throws NotFoundHttpException
     */
    public function deleteGalleryAction($id): FOSRestView
    {
        $gallery = $this->getGallery($id);

        $this->galleryManager->delete($gallery);

        return FOSRestView::create(['deleted' => true]);
    }

    /**
     * Write a GalleryItem, this method is used by both POST and PUT action methods.
     *
     * @return FOSRestView|FormInterface
     */
    private function handleWriteGalleryItem(GalleryInterface $gallery, MediaInterface $media, ?GalleryItemInterface $galleryItem = null, Request $request): object
    {
        $form = $this->formFactory->createNamed('', ApiGalleryItemType::class, $galleryItem, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $galleryItem = $form->getData();
            $galleryItem->setMedia($media);

            $gallery->addGalleryItem($galleryItem);
            $this->galleryManager->save($gallery);

            $view = FOSRestView::create($galleryItem);

            $context = new Context();
            $context->setGroups(['sonata_api_read']);
            $context->enableMaxDepth();

            $view->setContext($context);

            return $view;
        }

        return $form;
    }

    /**
     * Retrieves gallery with identifier $id or throws an exception if it doesn't exist.
     *
     * @param int|string $id Gallery identifier
     *
     * @throws NotFoundHttpException
     */
    private function getGallery($id): GalleryInterface
    {
        $gallery = $this->galleryManager->findOneBy(['id' => $id]);

        if (null === $gallery) {
            throw new NotFoundHttpException(sprintf('Gallery not found for identifier %s.', var_export($id, true)));
        }

        return $gallery;
    }

    /**
     * Retrieves media with identifier $id or throws an exception if it doesn't exist.
     *
     * @param int|string $id Media identifier
     *
     * @throws NotFoundHttpException
     */
    private function getMedia($id): MediaInterface
    {
        $media = $this->mediaManager->findOneBy(['id' => $id]);

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media not found for identifier %s.', var_export($id, true)));
        }

        return $media;
    }

    /**
     * Write a Gallery, this method is used by both POST and PUT action methods.
     *
     * @param int|string|null $id Gallery identifier
     *
     * @return FOSRestView|FormInterface
     */
    private function handleWriteGallery(Request $request, $id = null): object
    {
        $gallery = null !== $id ? $this->getGallery($id) : null;

        $form = $this->formFactory->createNamed('', ApiGalleryType::class, $gallery, [
            'csrf_protection' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $gallery = $form->getData();
            $this->galleryManager->save($gallery);

            $context = new Context();
            $context->setGroups(['sonata_api_read']);
            $context->enableMaxDepth();

            $view = FOSRestView::create($gallery);
            $view->setContext($context);

            return $view;
        }

        return $form;
    }
}
