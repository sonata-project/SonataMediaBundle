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
use Sonata\MediaBundle\Form\Type\ApiGalleryItemType;
use Sonata\MediaBundle\Form\Type\ApiGalleryType;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @final since sonata-project/media-bundle 3.21.0
 *
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
     * @param string $galleryItemClass
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
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"={"sonata_api_read"}}
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
     *     description="Number of galleries by page"
     * )
     * @Rest\QueryParam(
     *     name="enabled",
     *     requirements="0|1",
     *     nullable=true,
     *     strict=true,
     *     description="Enabled/Disabled galleries filter"
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

        return $this->getGalleryManager()->getPager($criteria, (int) $page, (int) $limit, $sort);
    }

    /**
     * Retrieves a specific gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Gallery identifier"}
     *  },
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Gallery identifier
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Gallery identifier"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Gallery identifier
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Gallery identifier"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\GalleryItem", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"sonata_api_read"}, serializerEnableMaxDepthChecks=true)
     *
     * @param string $id Gallery identifier
     *
     * @return GalleryItemInterface[]
     */
    public function getGalleryGalleryItemsAction($id)
    {
        return $this->getGallery($id)->getGalleryItems();
    }

    /**
     * Adds a gallery.
     *
     * @ApiDoc(
     *  input={"class"="sonata_media_api_form_gallery", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while gallery creation",
     *  }
     * )
     *
     * @param Request $request Symfony request
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Gallery identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_gallery", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while gallery creation",
     *      404="Returned when unable to find gallery"
     *  }
     * )
     *
     * @param string  $id      Gallery identifier
     * @param Request $request Symfony request
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
     * Adds a medium to a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="string", "description"="Gallery identifier"},
     *      {"name"="mediaId", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_gallery_item", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while gallery/media attachment",
     *  }
     * )
     *
     * @param string  $galleryId Gallery identifier
     * @param string  $mediaId   Medium identifier
     * @param Request $request   Symfony request
     *
     * @throws NotFoundHttpException
     *
     * @return GalleryInterface
     */
    public function postGalleryMediaGalleryItemAction($galleryId, $mediaId, Request $request)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);
        $galleryItemExists = $gallery->getGalleryItems()->exists(static function ($key, GalleryItemInterface $element) use ($media): bool {
            return $element->getMedia()->getId() === $media->getId();
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="string", "description"="Gallery identifier"},
     *      {"name"="mediaId", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_gallery_item", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when an error if medium cannot be found in gallery",
     *  }
     * )
     *
     * @param string  $galleryId Gallery identifier
     * @param string  $mediaId   Medium identifier
     * @param Request $request   Symfony request
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
     * Deletes a medium association to a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="string", "description"="Gallery identifier"},
     *      {"name"="mediaId", "dataType"="string", "description"="Medium identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when medium is successfully deleted from gallery",
     *      400="Returned when an error has occurred while medium deletion of gallery",
     *      404="Returned when unable to find gallery or media"
     *  }
     * )
     *
     * @param string $galleryId Gallery identifier
     * @param string $mediaId   Media identifier
     *
     * @throws NotFoundHttpException
     *
     * @return Rest\View
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="string", "description"="Gallery identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when gallery is successfully deleted",
     *      400="Returned when an error has occurred while gallery deletion",
     *      404="Returned when unable to find gallery"
     *  }
     * )
     *
     * @param string $id Gallery identifier
     *
     * @throws NotFoundHttpException
     *
     * @return Rest\View
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
     * @return FormInterface
     */
    protected function handleWriteGalleryItem(GalleryInterface $gallery, MediaInterface $media, ?GalleryItemInterface $galleryItem = null, Request $request)
    {
        $form = $this->formFactory->createNamed(null, ApiGalleryItemType::class, $galleryItem, [
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
     * @param string $id Gallery identifier
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
     * Retrieves media with identifier $id or throws an exception if it doesn't exist.
     *
     * @param string $id Media identifier
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
     * @param Request     $request Symfony request
     * @param string|null $id      Gallery identifier
     *
     * @return Rest\View|FormInterface
     */
    protected function handleWriteGallery($request, $id = null)
    {
        $gallery = $id ? $this->getGallery($id) : null;

        $form = $this->formFactory->createNamed(null, ApiGalleryType::class, $gallery, [
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
