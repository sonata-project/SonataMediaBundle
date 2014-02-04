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

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class MediaController
 *
 * @package Sonata\MediaBundle\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MediaController extends FOSRestController
{
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
        $media = $this->get('sonata.media.manager.media')->findOneBy(array('id' => $id));

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }
}