<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends Controller
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    public function getProvider(MediaInterface $media)
    {
        return $this->get('sonata.media.pool')->getProvider($media->getProviderName());
    }

    /**
     * @param $id
     * @return \Sonata\MediaBundle\Model\MediaInterface
     */
    public function getMedia($id)
    {
        return $this->get('sonata.media.manager.media')->findOneBy(array('id' => $id));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param $id
     * @param string $format
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function viewAction($id, $format = 'reference')
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        return $this->render('SonataMediaBundle:Media:view.html.twig', array(
            'media'     => $media,
            'formats'   => $this->getProvider($media)->getFormats(),
            'format'    => $format
        ));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @param $id
     * @param string $format
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function downloadAction($id, $format = 'reference')
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadSecurity($media)->isGranted($media, $this->getRequest())){
            throw new AccessDeniedException();
        }

        $response = $this->getProvider($media)
            ->getDownloadResponse($media, $format, $this->get('sonata.media.pool')->getDownloadMode($media));

        return $response;
    }
}