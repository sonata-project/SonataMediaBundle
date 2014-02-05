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

use Sonata\MediaBundle\Model\Media;

use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;


/**
 * Class MediaController
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
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor
     *
     * @param MediaManagerInterface $mediaManager
     * @param Pool                  $mediaPool
     * @param RouterInterface       $router
     */
    public function __construct(MediaManagerInterface $mediaManager, Pool $mediaPool, RouterInterface $router)
    {
        $this->mediaManager = $mediaManager;
        $this->mediaPool    = $mediaPool;
        $this->router       = $router;
    }

    /**
     * Retrieves a specific media
     *
     * @ApiDoc(
     *  resource=true,
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
    public function getMediaAction($id)
    {
        return $this->getMedia($id);
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
    public function getMediaFormatsAction($id)
    {
        $media = $this->getMedia($id);

        $formats = array('reference');
        $formats = array_merge($formats, array_keys($this->mediaPool->getFormatNamesByContext($media->getContext())));

        $properties = array();
        foreach ($formats as $format) {
            $properties[$format]['protected_url'] = $this->router->generate('sonata_media_download', array('id' => $id));
            $properties[$format]['properties'] = $this->mediaPool->getProvider($media->getProviderName())->getHelperProperties($media, $format);
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
    public function getMediaBinaryAction($id, $format, Request $request)
    {
        $media = $this->getMedia($id);

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
    protected function getMedia($id)
    {
        $media = $this->mediaManager->findOneBy(array('id' => $id));

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }
}