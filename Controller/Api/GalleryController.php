<?php

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
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GalleryController.
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
    protected $galleryHasMediaClass;

    /**
     * Constructor.
     *
     * @param GalleryManagerInterface $galleryManager
     * @param MediaManagerInterface   $mediaManager
     * @param FormFactoryInterface    $formFactory
     * @param string                  $galleryHasMediaClass
     */
    public function __construct(GalleryManagerInterface $galleryManager, MediaManagerInterface $mediaManager, FormFactoryInterface $formFactory, $galleryHasMediaClass)
    {
        $this->galleryManager = $galleryManager;
        $this->mediaManager = $mediaManager;
        $this->formFactory = $formFactory;
        $this->galleryHasMediaClass = $galleryHasMediaClass;
    }

    /**
     * Retrieves the list of galleries (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for gallery list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of galleries by page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled galleries filter")
     * @QueryParam(name="orderBy", array=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getGalleriesAction(ParamFetcherInterface $paramFetcher)
    {
        $supportedCriteria = array(
            'enabled' => '',
        );

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
            $sort = array();
        } elseif (!is_array($sort)) {
            $sort = array($sort => 'asc');
        }

        return $this->getGalleryManager()->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery id"}
     *  },
     *  output={"class"="sonata_media_api_form_gallery", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
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
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery id"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return MediaInterface[]
     */
    public function getGalleryMediasAction($id)
    {
        $ghms = $this->getGallery($id)->getGalleryHasMedias();

        $media = array();
        foreach ($ghms as $ghm) {
            $media[] = $ghm->getMedia();
        }

        return $media;
    }

    /**
     * Retrieves the galleryhasmedias of specified gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery id"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\GalleryHasMedia", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when gallery is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return GalleryHasMediaInterface[]
     */
    public function getGalleryGalleryhasmediasAction($id)
    {
        return $this->getGallery($id)->getGalleryHasMedias();
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
     * @param Request $request A Symfony request
     *
     * @return GalleryInterface
     *
     * @throws NotFoundHttpException
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
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery identifier"}
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
     * @param int     $id      User id
     * @param Request $request A Symfony request
     *
     * @return GalleryInterface
     *
     * @throws NotFoundHttpException
     */
    public function putGalleryAction($id, Request $request)
    {
        return $this->handleWriteGallery($request, $id);
    }

    /**
     * Adds a media to a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="integer", "requirement"="\d+", "description"="gallery identifier"},
     *      {"name"="mediaId", "dataType"="integer", "requirement"="\d+", "description"="media identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_gallery_has_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while gallery/media attachment",
     *  }
     * )
     *
     * @param int     $galleryId A gallery identifier
     * @param int     $mediaId   A media identifier
     * @param Request $request   A Symfony request
     *
     * @return GalleryInterface
     *
     * @throws NotFoundHttpException
     */
    public function postGalleryMediaGalleryhasmediaAction($galleryId, $mediaId, Request $request)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryHasMedias() as $galleryHasMedia) {
            if ($galleryHasMedia->getMedia()->getId() == $media->getId()) {
                return FOSRestView::create(array(
                    'error' => sprintf('Gallery "%s" already has media "%s"', $galleryId, $mediaId),
                ), 400);
            }
        }

        return $this->handleWriteGalleryhasmedia($gallery, $media, null, $request);
    }

    /**
     * Updates a media to a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="integer", "requirement"="\d+", "description"="gallery identifier"},
     *      {"name"="mediaId", "dataType"="integer", "requirement"="\d+", "description"="media identifier"}
     *  },
     *  input={"class"="sonata_media_api_form_gallery_has_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="sonata_media_api_form_gallery", "groups"={"sonata_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when an error if media cannot be found in gallery",
     *  }
     * )
     *
     * @param int     $galleryId A gallery identifier
     * @param int     $mediaId   A media identifier
     * @param Request $request   A Symfony request
     *
     * @return GalleryInterface
     *
     * @throws NotFoundHttpException
     */
    public function putGalleryMediaGalleryhasmediaAction($galleryId, $mediaId, Request $request)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryHasMedias() as $galleryHasMedia) {
            if ($galleryHasMedia->getMedia()->getId() == $media->getId()) {
                return $this->handleWriteGalleryhasmedia($gallery, $media, $galleryHasMedia, $request);
            }
        }

        throw new NotFoundHttpException(sprintf('Gallery "%s" does not have media "%s"', $galleryId, $mediaId));
    }

    /**
     * Deletes a media association to a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="galleryId", "dataType"="integer", "requirement"="\d+", "description"="gallery identifier"},
     *      {"name"="mediaId", "dataType"="integer", "requirement"="\d+", "description"="media identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when media is successfully deleted from gallery",
     *      400="Returned when an error has occurred while media deletion of gallery",
     *      404="Returned when unable to find gallery or media"
     *  }
     * )
     *
     * @param int $galleryId A gallery identifier
     * @param int $mediaId   A media identifier
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function deleteGalleryMediaGalleryhasmediaAction($galleryId, $mediaId)
    {
        $gallery = $this->getGallery($galleryId);
        $media = $this->getMedia($mediaId);

        foreach ($gallery->getGalleryHasMedias() as $key => $galleryHasMedia) {
            if ($galleryHasMedia->getMedia()->getId() == $media->getId()) {
                $gallery->getGalleryHasMedias()->remove($key);
                $this->getGalleryManager()->save($gallery);

                return array('deleted' => true);
            }
        }

        return FOSRestView::create(array(
            'error' => sprintf('Gallery "%s" does not have media "%s" associated', $galleryId, $mediaId),
        ), 400);
    }

    /**
     * Deletes a gallery.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when gallery is successfully deleted",
     *      400="Returned when an error has occurred while gallery deletion",
     *      404="Returned when unable to find gallery"
     *  }
     * )
     *
     * @param int $id A Gallery identifier
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function deleteGalleryAction($id)
    {
        $gallery = $this->getGallery($id);

        $this->galleryManager->delete($gallery);

        return array('deleted' => true);
    }

    /**
     * Write a GalleryHasMedia, this method is used by both POST and PUT action methods.
     *
     * @param GalleryInterface         $gallery
     * @param MediaInterface           $media
     * @param GalleryHasMediaInterface $galleryHasMedia
     * @param Request                  $request
     *
     * @return FormInterface
     */
    protected function handleWriteGalleryhasmedia(GalleryInterface $gallery, MediaInterface $media, GalleryHasMediaInterface $galleryHasMedia = null, Request $request)
    {
        $form = $this->formFactory->createNamed(null, 'sonata_media_api_form_gallery_has_media', $galleryHasMedia, array(
            'csrf_protection' => false,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $galleryHasMedia = $form->getData();
            $galleryHasMedia->setMedia($media);

            $gallery->addGalleryHasMedias($galleryHasMedia);
            $this->galleryManager->save($gallery);

            $view = FOSRestView::create($galleryHasMedia);

            // BC for FOSRestBundle < 2.0
            if (method_exists($view, 'setSerializationContext')) {
                $serializationContext = SerializationContext::create();
                $serializationContext->setGroups(array('sonata_api_read'));
                $serializationContext->enableMaxDepthChecks();
                $view->setSerializationContext($serializationContext);
            } else {
                $context = new Context();
                $context->setGroups(array('sonata_api_read'));
                $context->setMaxDepth(0);
                $view->setContext($context);
            }

            return $view;
        }

        return $form;
    }

    /**
     * Retrieves gallery with id $id or throws an exception if it doesn't exist.
     *
     * @param $id
     *
     * @return GalleryInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getGallery($id)
    {
        $gallery = $this->getGalleryManager()->findOneBy(array('id' => $id));

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
     * @return MediaInterface
     *
     * @throws NotFoundHttpException
     */
    protected function getMedia($id)
    {
        $media = $this->getMediaManager()->findOneBy(array('id' => $id));

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

        $form = $this->formFactory->createNamed(null, 'sonata_media_api_form_gallery', $gallery, array(
            'csrf_protection' => false,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $gallery = $form->getData();
            $this->galleryManager->save($gallery);

            $view = FOSRestView::create($gallery);

            // BC for FOSRestBundle < 2.0
            if (method_exists($view, 'setSerializationContext')) {
                $serializationContext = SerializationContext::create();
                $serializationContext->setGroups(array('sonata_api_read'));
                $serializationContext->enableMaxDepthChecks();
                $view->setSerializationContext($serializationContext);
            } else {
                $context = new Context();
                $context->setGroups(array('sonata_api_read'));
                $context->setMaxDepth(0);
                $view->setContext($context);
            }

            return $view;
        }

        return $form;
    }
}
