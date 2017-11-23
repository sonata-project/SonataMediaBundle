<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Controller;

use InvalidArgumentException;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaController extends Controller
{
    /**
     * @param MediaInterface $media
     *
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
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function downloadAction($id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadSecurity($media)->isGranted($media, $this->getCurrentRequest())) {
            throw new AccessDeniedException();
        }

        $response = $this->getProvider($media)->getDownloadResponse($media, $format, $this->get('sonata.media.pool')->getDownloadMode($media));

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($this->getCurrentRequest());
        }

        return $response;
    }

    /**
     * @param string $id
     * @param string $format
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function viewAction($id, $format = MediaProviderInterface::FORMAT_REFERENCE)
    {
        $media = $this->getMedia($id);

        if (!$media) {
            throw new NotFoundHttpException(sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->get('sonata.media.pool')->getDownloadSecurity($media)->isGranted($media, $this->getCurrentRequest())) {
            throw new AccessDeniedException();
        }

        return $this->render('SonataMediaBundle:Media:view.html.twig', [
            'media' => $media,
            'formats' => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
            'format' => $format,
        ]);
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param string $path
     * @param string $filter
     *
     * @return RedirectResponse
     *
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     */
    public function liipImagineFilterAction($path, $filter)
    {
        $path = urldecode($path);
        $resolver = $this->getCurrentRequest()->get('resolver');

        if (!$this->get('liip_imagine.cache.manager')->isStored($path, $filter, $resolver)) {
            if (!preg_match('@([^/]*)/(.*)/([0-9]*)_([a-z_A-Z]*).jpg@', $path, $matches)) {
                throw new NotFoundHttpException();
            }

            $media = $this->getMedia($matches[3]);
            if (!$media) {
                throw new NotFoundHttpException();
            }

            $provider = $this->getProvider($media);
            $file = $provider->getReferenceFile($media);
            $binary = $this->get('liip_imagine.data.manager')->find($filter, '/uploads/media/'.$file->getKey());

            $this->get('liip_imagine.cache.manager')->store(
                $this->get('liip_imagine.filter.manager')->applyFilter($binary, $filter),
                $path,
                $filter,
                $resolver
            );
        }

        return new RedirectResponse($this->get('liip_imagine.cache.manager')->resolve($path, $filter, $resolver), 301);
    }

    /**
     * NEXT_MAJOR: Remove this method when bumping Symfony requirement to 2.8+.
     * Inject the Symfony\Component\HttpFoundation\Request into the actions instead.
     *
     * @return Request
     */
    private function getCurrentRequest()
    {
        if ($this->has('request_stack')) {
            return $this->get('request_stack')->getCurrentRequest();
        }

        return $this->get('request');
    }
}
