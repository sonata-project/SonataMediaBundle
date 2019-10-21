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

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class Pixlr
{
    /**
     * @var string
     */
    protected $referrer;

    /**
     * @var string
     */
    protected $secret;

    /**
     * @var MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var string[]
     */
    protected $validFormats;

    /**
     * @var string
     */
    protected $allowEreg;

    /**
     * @param string $referrer
     * @param string $secret
     */
    public function __construct($referrer, $secret, Pool $pool, MediaManagerInterface $mediaManager, RouterInterface $router, EngineInterface $templating, ContainerInterface $container)
    {
        $this->referrer = $referrer;
        $this->secret = $secret;
        $this->mediaManager = $mediaManager;
        $this->router = $router;
        $this->pool = $pool;
        $this->templating = $templating;
        $this->container = $container;

        $this->validFormats = ['jpg', 'jpeg', 'png'];
        $this->allowEreg = '@https?://([a-zA-Z0-9]*).pixlr.com/_temp/[0-9a-z]{24}\.[a-z]*@';
    }

    /**
     * @param string $id
     * @param string $mode
     *
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function editAction($id, $mode)
    {
        if (!\in_array($mode, ['express', 'editor'], true)) {
            throw new NotFoundHttpException('Invalid mode');
        }

        $media = $this->getMedia($id);

        $provider = $this->pool->getProvider($media->getProviderName());

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

        $url = sprintf('https://pixlr.com/%s/?%s', $mode, $this->buildQuery($parameters));

        return new RedirectResponse($url);
    }

    /**
     * @param string $hash
     * @param string $id
     *
     * @return Response
     */
    public function exitAction($hash, $id)
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        return new Response($this->templating->render('@SonataMedia/Extra/pixlr_exit.html.twig'));
    }

    /**
     * @param string $hash
     * @param string $id
     *
     * @return Response
     */
    public function targetAction(Request $request, $hash, $id)
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        $provider = $this->pool->getProvider($media->getProviderName());

        /*
         * Pixlr send back the new image as an url, add some security check before downloading the file
         */
        if (!preg_match($this->allowEreg, $request->get('image'), $matches)) {
            throw new NotFoundHttpException(sprintf('Invalid image host : %s', $request->get('image')));
        }

        $file = $provider->getReferenceFile($media);
        $file->setContent(file_get_contents($request->get('image')));

        $provider->updateMetadata($media);
        $provider->generateThumbnails($media);

        $this->mediaManager->save($media);

        return new Response($this->templating->render('@SonataMedia/Extra/pixlr_exit.html.twig'));
    }

    /**
     * @return bool
     */
    public function isEditable(MediaInterface $media)
    {
        if (!$this->container->get('sonata.media.admin.media')->isGranted('EDIT', $media)) {
            return false;
        }

        return \in_array(strtolower($media->getExtension()), $this->validFormats, true);
    }

    /**
     * @param string $id
     *
     * @throws NotFoundHttpException
     *
     * @return Response
     */
    public function openEditorAction($id)
    {
        $media = $this->getMedia($id);

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('The media is not editable');
        }

        return new Response($this->templating->render('@SonataMedia/Extra/pixlr_editor.html.twig', [
            'media' => $media,
            'admin_pool' => $this->container->get('sonata.admin.pool'),
        ]));
    }

    private function generateHash(MediaInterface $media): string
    {
        return sha1($media->getId().$media->getCreatedAt()->format('u').$this->secret);
    }

    /**
     * @throws NotFoundHttpException
     */
    private function getMedia(string $id): MediaInterface
    {
        $media = $this->mediaManager->findOneBy(['id' => $id]);

        if (!$media) {
            throw new NotFoundHttpException('Media not found');
        }

        return $media;
    }

    /**
     * @throws NotFoundHttpException
     */
    private function checkMedia(string $hash, MediaInterface $media)
    {
        if ($hash !== $this->generateHash($media)) {
            throw new NotFoundHttpException('Invalid hash');
        }

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('Media is not editable');
        }
    }

    private function buildQuery(array $parameters = []): string
    {
        $query = [];
        foreach ($parameters as $name => $value) {
            $query[] = sprintf('%s=%s', $name, $value);
        }

        return implode('&', $query);
    }
}
