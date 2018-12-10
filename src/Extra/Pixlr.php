<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Extra;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool as AdminPool;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

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
     * @var ManagerInterface
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
     * @var string[]
     */
    protected $validFormats;

    /**
     * @var string
     */
    protected $allowEreg;

    /**
     * @var AdminPool
     */
    protected $adminPool;

    /**
     * @var AdminInterface
     */
    protected $mediaAdmin;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @param string           $referrer
     * @param string           $secret
     * @param Pool             $pool
     * @param ManagerInterface $mediaManager
     * @param RouterInterface  $router
     * @param EngineInterface  $templating
     * @param EngineInterface  $templating
     * @param AdminPool        $templating
     * @param AdminInterface   $mediaAdmin
     * @param RequestStack     $requestStack
     */
    public function __construct($referrer, $secret, Pool $pool, ManagerInterface $mediaManager, RouterInterface $router, EngineInterface $templating, AdminPool $adminPool, AdminInterface $mediaAdmin, RequestStack $requestStack)
    {
        $this->referrer = $referrer;
        $this->secret = $secret;
        $this->mediaManager = $mediaManager;
        $this->router = $router;
        $this->pool = $pool;
        $this->templating = $templating;
        $this->adminPool = $adminPool;
        $this->mediaAdmin = $mediaAdmin;
        $this->requestStack = $requestStack;

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
        if (!\in_array($mode, ['express', 'editor'])) {
            throw new NotFoundHttpException('Invalid mode');
        }

        $media = $this->getMedia($id);
        $hash = $this->generateHash($media);

        $parameters = [
            's' => 'c', // ??
            'referrer' => $this->referrer,
            'exit' => $this->router->generate('sonata_media_pixlr_exit', ['hash' => $hash, 'id' => $media->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'image' => $this->generateImageUrl($media),
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
     * @param Request $request
     * @param string  $hash
     * @param string  $id
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
     * @param MediaInterface $media
     *
     * @return bool
     */
    public function isEditable(MediaInterface $media)
    {
        if (!$this->mediaAdmin->isGranted('EDIT', $media)) {
            return false;
        }

        return \in_array(strtolower($media->getExtension()), $this->validFormats);
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
            'admin_pool' => $this->adminPool,
        ]));
    }

    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    protected function generateImageUrl(MediaInterface $media)
    {
        $provider = $this->pool->getProvider($media->getProviderName());
        $imageUrl = $provider->generatePublicUrl($media, MediaProviderInterface::FORMAT_REFERENCE);

        if (false !== filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return $imageUrl;
        }

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            return $request->getUriForPath($imageUrl);
        }

        return $imageUrl;
    }

    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    private function generateHash(MediaInterface $media)
    {
        return sha1($media->getId().$media->getCreatedAt()->format('u').$this->secret);
    }

    /**
     * @param string $id
     *
     * @throws NotFoundHttpException
     *
     * @return MediaInterface
     */
    private function getMedia($id)
    {
        $media = $this->mediaManager->findOneBy(['id' => $id]);

        if (!$media) {
            throw new NotFoundHttpException('Media not found');
        }

        return $media;
    }

    /**
     * @param string         $hash
     * @param MediaInterface $media
     *
     * @throws NotFoundHttpException
     */
    private function checkMedia($hash, MediaInterface $media)
    {
        if ($hash != $this->generateHash($media)) {
            throw new NotFoundHttpException('Invalid hash');
        }

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('Media is not editable');
        }
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    private function buildQuery(array $parameters = [])
    {
        $query = [];
        foreach ($parameters as $name => $value) {
            $query[] = sprintf('%s=%s', $name, $value);
        }

        return implode('&', $query);
    }
}
