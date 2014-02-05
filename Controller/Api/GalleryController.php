<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\MediaBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;

use FOS\RestBundle\Request\ParamFetcherInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryHasMedia;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class GalleryController
 *
 * @package Sonata\MediaBundle\Controller\Api
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
     * Constructor
     *
     * @param GalleryManagerInterface $galleryManager
     */
    public function __construct(GalleryManagerInterface $galleryManager)
    {
        $this->galleryManager = $galleryManager;
    }

    /**
     * Retrieves the list of galleries (paginated)
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\MediaBundle\Model\Gallery", "groups"="sonata_api_read"}
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
     * @return Gallery[]
     */
    public function getGalleriesAction(ParamFetcherInterface $paramFetcher)
    {
        $page    = $paramFetcher->get('page');
        $count   = $paramFetcher->get('count');
        $orderBy = $paramFetcher->get('orderBy');

        $criteria = $paramFetcher->all();

        unset($criteria['page'], $criteria['count'], $criteria['orderBy']);

        foreach ($criteria as $key => $crit) {
            if (null === $crit) {
                unset($criteria[$key]);
            }
        }

        return $this->getGalleryManager()->findBy($criteria, $orderBy, $count, $page);
    }

    /**
     * Retrieves a specific gallery
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="gallery id"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\Gallery", "groups"="sonata_api_read"},
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
     * @return Gallery
     */
    public function getGalleryAction($id)
    {
        return $this->getGallery($id);
    }

    /**
     * Retrieves the medias of specified gallery
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
     * @return Media[]
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
     * Retrieves the galleryhasmedias of specified gallery
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
     * @return GalleryHasMedia[]
     */
    public function getGalleryGalleryhasmediasAction($id)
    {
        return $this->getGallery($id)->getGalleryHasMedias();
    }

    /**
     * Retrieves gallery with id $id or throws an exception if it doesn't exist
     *
     * @param $id
     *
     * @return Gallery
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @return GalleryManagerInterface
     */
    protected function getGalleryManager()
    {
        return $this->galleryManager;
    }
}