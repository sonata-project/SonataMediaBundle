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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryHasMedia;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Component\Form\FormInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View as FOSRestView;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryController
{
    /**
     * @var GalleryManagerInterface
     */
    protected $galleryManager;

    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $galleryItemClass;

    /**
     * Constructor.
     *
     * @param GalleryManagerInterface $galleryManager
     * @param MediaManagerInterface   $mediaManager
     * @param FormFactoryInterface    $formFactory
     * @param string                  $galleryItemClass
     */
    public function __construct(GalleryManagerInterface $galleryManager, MediaManagerInterface $mediaManager, FormFactoryInterface $formFactory, $galleryItemClass)
    {
        $this->galleryManager = $galleryManager;
        $this->mediaManager = $mediaManager;
        $this->formFactory = $formFactory;
        $this->galleryItemClass = $galleryItemClass;
    }

    /**
     * Retrieves the list of galleries (paginated).
     *
     * @Operation(
     *     tags={""},
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
     *         description="Enabled/Disabled galleries filter",
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
     * @QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     default="1",
     *     description="Page for gallery list pagination"
     * )
     * @QueryParam(
     *     name="count",
     *     requirements="\d+",
     *     default="10",
     *     description="Number of galleries by page"
     * )
     * @QueryParam(
     *     name="enabled",
     *     requirements="0|1",
     *     nullable=true,
     *     strict=true,
     *     description="Enabled/Disabled galleries filter"
     * )
     * @QueryParam(
     *     name="orderBy",
     *     map=true,
     *     requirements="ASC|DESC",
     *     nullable=true,
     *     strict=true,
     *     description="Order by array (key is field, value is direction)"
     * )
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getGalleriesAction(ParamFetcherInterface $paramFetcher)
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

        return $this->getGalleryManager()->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Retrieves a specific gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return GalleryInterface
     */
    public function getGalleryAction($id)
    {
        return $this->getGallery($id);
    }

    /**
     * Retrieves the medias of specified gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Retrieves the medias of specified gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\Media",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return MediaInterface[]
     */
    public function getGalleryMediasAction($id)
    {
        $galleryItems = $this->getGallery($id)->getGalleryItems();

        $media = [];
        foreach ($galleryItems as $galleryItem) {
            $media[] = $galleryItem->getMedia();
        }

        return $media;
    }

    /**
     * Retrieves the gallery items of specified gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Retrieves the gallery items of specified gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Model\GalleryItem",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when gallery is not found"
     *     )
     * )
     *
     *
     * @View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return GalleryItemInterface[]
     */
    public function getGalleryGalleryItemAction($id)
    {
        return $this->getGallery($id)->getGalleryItems();
    }

    /**
     * Adds a gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Adds a gallery.",
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Gallery data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery creation"
     *     )
     * )
     *
     *
     * @param Request $request A Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    public function postGalleryAction(Request $request)
    {
        return $this->handleWriteGallery($request);
    }

    /**
     * Updates a gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Updates a gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="id",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Gallery data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType"))
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
     *
     * @param int     $id      User id
     * @param Request $request A Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    public function putGalleryAction($id, Request $request)
    {
        return $this->handleWriteGallery($request, $id);
    }

    /**
     * Adds a media to a gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Adds a media to a gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="galleryId",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="integer",
     *          name="mediaId",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Gallery data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryItemType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType",groups={"sonata_api_read"}))
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while gallery/media attachment"
     *     )
     * )
     *
     *
     * @param int     $galleryId A gallery identifier
     * @param int     $mediaId   A media identifier
     * @param Request $request   A Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    public function postGalleryMediaGalleryItemAction($galleryId, $mediaId, Request $request)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryItems() as $galleryItem) {
            if ($galleryItem->getMedia()->getId() === $media->getId()) {
                return FOSRestView::create([
                    'error' => sprintf('Gallery "%s" already has media "%s"', $galleryId, $mediaId),
                ], 400);
            }
        }

        return $this->handleWriteGalleryItem($gallery, $media, null, $request);
    }

    /**
     * Updates a media to a gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Updates a media to a gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="galleryId",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="integer",
     *          name="mediaId",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="object",
     *          name="",
     *          in="body",
     *          description="Gallery data",
     *          required=true,
     *          @Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryItemType",groups={"sonata_api_write"})
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful",
     *         @SWG\Schema(ref=@Model(type="Sonata\MediaBundle\Form\Type\ApiGalleryType"))
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when an error if media cannot be found in gallery"
     *     )
     * )
     *
     *
     * @param int     $galleryId A gallery identifier
     * @param int     $mediaId   A media identifier
     * @param Request $request   A Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    public function putGalleryMediaGalleryItemAction($galleryId, $mediaId, Request $request)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryItems() as $galleryItem) {
            if ($galleryItem->getMedia()->getId() === $media->getId()) {
                return $this->handleWriteGalleryItem($gallery, $media, $galleryItem, $request);
            }
        }

        throw new NotFoundHttpException(sprintf('Gallery "%s" does not have media "%s"', $galleryId, $mediaId));
    }

    /**
     * Deletes a media association to a gallery.
     *
     * @Operation(
     *     tags={""},
     *     summary="Deletes a media association to a gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="galleryId",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          type="integer",
     *          name="mediaId",
     *          in="path",
     *          description="media id",
     *          required=true,
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when media is successfully deleted from gallery"
     *     ),
     *     @SWG\Response(
     *         response="400",
     *         description="Returned when an error has occurred while media deletion of gallery"
     *     ),
     *     @SWG\Response(
     *         response="404",
     *         description="Returned when unable to find gallery or media"
     *     )
     * )
     *
     *
     * @param int $galleryId A gallery identifier
     * @param int $mediaId   A media identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function deleteGalleryMediaGalleryItemAction($galleryId, $mediaId)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryItems() as $key => $galleryItem) {
            if ($galleryItem->getMedia()->getId() === $media->getId()) {
                $gallery->getGalleryItems()->remove($key);
                $this->getGalleryManager()->save($gallery);

                return ['deleted' => true];
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
     *     tags={""},
     *     summary="Deletes a gallery.",
     *     @SWG\Parameter(
     *          type="integer",
     *          name="galleryId",
     *          in="path",
     *          description="gallery id",
     *          required=true,
     *     ),
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
     *
     * @param int $id A Gallery identifier
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function deleteGalleryAction($id)
    {
        $gallery = $this->getGallery($id);

        $this->galleryManager->delete($gallery);

        return ['deleted' => true];
    }

    /**
     * Write a GalleryItem, this method is used by both POST and PUT action methods.
     *
     * @param GalleryInterface     $gallery
     * @param MediaInterface       $media
     * @param GalleryItemInterface $galleryItem
     * @param Request              $request
     *
     * @return FormInterface
     */
    protected function handleWriteGalleryItem(GalleryInterface $gallery, MediaInterface $media, GalleryItemInterface $galleryItem = null, Request $request)
    {
        $form = $this->formFactory->createNamed(null, 'sonata_media_api_form_gallery_item', $galleryItem, [
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
     * Retrieves gallery with id $id or throws an exception if it doesn't exist.
     *
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    protected function getGallery($id)
    {
        $gallery = $this->getGalleryManager()->findOneBy(['id' => $id]);

        if (null === $gallery) {
            throw new NotFoundHttpException(sprintf('Gallery (%d) not found', $id));
        }

        return $gallery;
    }

    /**
     * Retrieves media with id $id or throws an exception if it doesn't exist.
     *
     * @param $id
     *
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    protected function getMedia($id)
    {
        $media = $this->getMediaManager()->findOneBy(['id' => $id]);

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) not found', $id));
        }

        return $media;
    }

    /**
     * @return GalleryManagerInterface
     */
    protected function getGalleryManager()
    {
        return $this->galleryManager;
    }

    /**
     * @return MediaManagerInterface
     */
    protected function getMediaManager()
    {
        return $this->mediaManager;
    }

    /**
     * Write a Gallery, this method is used by both POST and PUT action methods.
     *
     * @param Request  $request Symfony request
     * @param int|null $id      A Gallery identifier
     *
     * @return View|FormInterface
     */
    protected function handleWriteGallery($request, $id = null)
    {
        $gallery = $id ? $this->getGallery($id) : null;

        $form = $this->formFactory->createNamed(null, 'sonata_media_api_form_gallery', $gallery, [
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
