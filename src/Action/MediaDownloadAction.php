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

namespace Sonata\MediaBundle\Action;

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class MediaDownloadAction
{
    public function __construct(
        private MediaManagerInterface $mediaManager,
        private Pool $pool
    ) {
    }

    /**
     * @param int|string $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function __invoke(Request $request, $id, string $format = MediaProviderInterface::FORMAT_REFERENCE): Response
    {
        $media = $this->mediaManager->find($id);

        if (null === $media) {
            throw new NotFoundHttpException(\sprintf('unable to find the media with the id : %s', $id));
        }

        if (!$this->pool->getDownloadStrategy($media)->isGranted($media, $request)) {
            throw new AccessDeniedException();
        }

        $response = $this->pool->getProvider($media->getProviderName())->getDownloadResponse(
            $media,
            $format,
            $this->pool->getDownloadMode($media)
        );

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($request);
        }

        return $response;
    }
}
