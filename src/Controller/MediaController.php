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

namespace Sonata\MediaBundle\Controller;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class MediaController extends AbstractController
{
    /**
     * @return MediaProviderInterface
     */
    public function getProvider(MediaInterface $media)
    {
        return $this->get('sonata.media.pool')->getProvider($media->getProviderName());
    }

    /**
     * @param string $id
     *
     * @return MediaInterface
     */
    public function getMedia($id)
    {
        return $this->get('sonata.media.manager.media')->find($id);
    }

    /**
     * @param string $id
     * @param string $format
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function downloadAction(Request $request, $id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }

        $response = $this->getProvider($media)->getDownloadResponse($media, $format, $this->get('sonata.media.pool')->getDownloadMode($media));

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($request);
        }

        return $response;
    }

    /**
     * @param string $id
     * @param string $format
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function viewAction(Request $request, $id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }

        return $this->render('@SonataMedia/Media/view.html.twig', [
            'media' => $media,
            'formats' => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
            'format' => $format,
        ]);
    }
}
