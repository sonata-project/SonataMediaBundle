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

namespace Sonata\MediaBundle\Extra;

use Liip\ImagineBundle\Exception\Config\Filter\NotFoundException;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool as AdminPool;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool as MediaPool;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

/**
 * NEXT_MAJOR: Remove this file.
 *
 * @deprecated since sonata-project/media-bundle 3.x, to be removed in 4.0.
 */
final class Pixlr
{
    /**
     * @var string
     */
    private $referrer;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var MediaPool
     */
    private $mediaPool;

    /**
     * @var AdminPool
     */
    private $adminPool;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var AdminInterface<MediaInterface>
     */
    private $mediaAdmin;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var string[]
     */
    private $validFormats;

    /**
     * @var string
     */
    private $allowEreg;

    /**
     * @param AdminInterface<MediaInterface> $mediaAdmin
     */
    public function __construct(
        string $referrer,
        string $secret,
        MediaPool $mediaPool,
        AdminPool $adminPool,
        MediaManagerInterface $mediaManager,
        AdminInterface $mediaAdmin,
        RouterInterface $router,
        Environment $twig
    ) {
        $this->referrer = $referrer;
        $this->secret = $secret;
        $this->mediaPool = $mediaPool;
        $this->adminPool = $adminPool;
        $this->mediaManager = $mediaManager;
        $this->mediaAdmin = $mediaAdmin;
        $this->router = $router;
        $this->twig = $twig;

        $this->validFormats = ['jpg', 'jpeg', 'png'];
        $this->allowEreg = '@https?://([a-zA-Z0-9]*).pixlr.com/_temp/[0-9a-z]{24}\.[a-z]*@';
    }

    /**
     * @throws NotFoundHttpException
     */
    public function editAction(string $id, string $mode): Response
    {
        if (!\in_array($mode, ['express', 'editor'], true)) {
            throw new NotFoundHttpException('Invalid mode');
        }

        $media = $this->getMedia($id);

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $hash = $this->generateHash($media);

        $parameters = [
            's' => 'c', // ??
            'referrer' => $this->referrer,
            'exit' => $this->router->generate('sonata_media_pixlr_exit', ['hash' => $hash, 'id' => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'image' => $provider->generatePublicUrl($media, MediaProviderInterface::FORMAT_REFERENCE),
            'title' => $media->getName(),
            'target' => $this->router->generate('sonata_media_pixlr_target', ['hash' => $hash, 'id' => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'locktitle' => true,
            'locktarget' => true,
        ];

        $url = sprintf('https://pixlr.com/%s/?%s', $mode, http_build_query($parameters));

        return new RedirectResponse($url);
    }

    public function exitAction(string $hash, string $id): Response
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        return new Response($this->twig->render('@SonataMedia/Extra/pixlr_exit.html.twig'));
    }

    public function targetAction(Request $request, string $hash, string $id): Response
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        $provider = $this->mediaPool->getProvider($media->getProviderName());

        $image = $request->get('image');

        /*
         * Pixlr send back the new image as an url, add some security check before downloading the file
         */
        if (1 !== preg_match($this->allowEreg, $image, $matches)) {
            throw new NotFoundHttpException(sprintf('Invalid image host : %s', $image));
        }

        $file = $provider->getReferenceFile($media);
        $fileContents = file_get_contents($image);

        if (false === $fileContents) {
            throw new NotFoundException(sprintf('Unable to open image: %s', $image));
        }

        $file->setContent($fileContents);

        $provider->updateMetadata($media);
        $provider->generateThumbnails($media);

        $this->mediaManager->save($media);

        return new Response($this->twig->render('@SonataMedia/Extra/pixlr_exit.html.twig'));
    }

    public function isEditable(MediaInterface $media): bool
    {
        if (!$this->mediaAdmin->isGranted('EDIT', $media)) {
            return false;
        }

        $extension = $media->getExtension();

        if (null === $extension) {
            return false;
        }

        return \in_array(strtolower($extension), $this->validFormats, true);
    }

    /**
     * @throws NotFoundHttpException
     */
    public function openEditorAction(string $id): Response
    {
        $media = $this->getMedia($id);

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('The media is not editable');
        }

        return new Response($this->twig->render('@SonataMedia/Extra/pixlr_editor.html.twig', [
            'media' => $media,
            'admin_pool' => $this->adminPool,
        ]));
    }

    private function generateHash(MediaInterface $media): string
    {
        $createdAt = $media->getCreatedAt();

        return sha1(sprintf('%s%s%s', $media->getId() ?? '', null !== $createdAt ? $createdAt->format('u') : '', $this->secret));
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getMedia(string $id): MediaInterface
    {
        $media = $this->mediaManager->findOneBy(['id' => $id]);

        if (null === $media) {
            throw new NotFoundHttpException('Media not found');
        }

        return $media;
    }

    /**
     * @throws NotFoundHttpException
     */
    private function checkMedia(string $hash, MediaInterface $media): void
    {
        if ($hash !== $this->generateHash($media)) {
            throw new NotFoundHttpException('Invalid hash');
        }

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('Media is not editable');
        }
    }
}
