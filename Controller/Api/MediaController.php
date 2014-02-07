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

use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

/**
 * Class MediaController
 *
 * Note: Media is plural, medium is singular (at least according to FOSRestBundle route generator)
 *
 * @package Sonata\MediaBundle\Controller\Api
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
     * Constructor
     *
     * @param MediaManagerInterface $mediaManager
     * @param Pool                  $mediaPool
     */
    public function __construct(MediaManagerInterface $mediaManager, Pool $mediaPool)
    {
        $this->mediaManager = $mediaManager;
        $this->mediaPool    = $mediaPool;
    }

    /**
     * Retrieves the list of medias (paginated)
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"="sonata_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page for media list pagination")
     * @QueryParam(name="count", requirements="\d+", default="10", description="Number of medias by page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled medias filter")
     * @QueryParam(name="orderBy", array=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Media[]
     */
    public function getMediaAction(ParamFetcherInterface $paramFetcher)
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

        return $this->mediaManager->findBy($criteria, $orderBy, $count, $page);
    }

    /**
     * Retrieves a specific media
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  output={"class"="Sonata\MediaBundle\Model\Media", "groups"="sonata_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @View(serializerGroups="sonata_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return Media
     */
    public function getMediumAction($id)
    {
        return $this->getMedium($id);
    }

    /**
     * Returns media urls for each format
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param $id
     *
     * @return array
     */
    public function getMediumFormatsAction($id)
    {
        $media = $this->getMedium($id);

        $formats = array('reference');
        $formats = array_merge($formats, array_keys($this->mediaPool->getFormatNamesByContext($media->getContext())));

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $properties = array();
        foreach ($formats as $format) {
            $properties[$format]['url']        = $provider->generatePublicUrl($media, $format);
            $properties[$format]['properties'] = $provider->getHelperProperties($media, $format);
        }

        return $properties;
    }

    /**
     * Returns media urls for each format
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"},
     *      {"name"="format", "dataType"="string", "description"="media format"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param integer $id     The media id
     * @param string  $format The format
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
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
     * Retrieves media with id $id or throws an exception if not found
     *
     * @param $id
     *
     * @return Media
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getMedium($id)
    {
        $media = $this->mediaManager->findOneBy(array('id' => $id));

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }
}