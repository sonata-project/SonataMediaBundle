<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Extra;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Pixlr
{
    protected $referrer;

    protected $secret;

    protected $mediaManager;

    protected $router;

    protected $pool;

    protected $templating;

    protected $container;

    protected $validFormats;

    protected $allowEreg;

    /**
     * @param string                                                    $referrer
     * @param string                                                    $secret
     * @param \Sonata\MediaBundle\Provider\Pool                         $pool
     * @param \Sonata\MediaBundle\Model\MediaManagerInterface           $mediaManager
     * @param \Symfony\Component\Routing\RouterInterface                $router
     * @param \Symfony\Component\Templating\EngineInterface             $templating
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct($referrer, $secret, Pool $pool, MediaManagerInterface $mediaManager, RouterInterface $router, EngineInterface $templating, ContainerInterface $container)
    {
        $this->referrer     = $referrer;
        $this->secret       = $secret;
        $this->mediaManager = $mediaManager;
        $this->router       = $router;
        $this->pool         = $pool;
        $this->templating   = $templating;
        $this->container    = $container;

        $this->validFormats = array('jpg', 'jpeg', 'png');
        $this->allowEreg    = '@http://([a-zA-Z0-9]*).pixlr.com/_temp/[0-9a-z]{24}\.[a-z]*@';
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return string
     */
    private function generateHash(MediaInterface $media)
    {
        return sha1($media->getId() . $media->getCreatedAt()->format('u') . $this->secret);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $id
     *
     * @return \Sonata\MediaBundle\Model\MediaInterface
     */
    private function getMedia($id)
    {
        $media = $this->mediaManager->findOneBy(array('id' => $id));

        if (!$media) {
            throw new NotFoundHttpException('Media not found');
        }

        return $media;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string                                   $hash
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return void
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
    private function buildQuery(array $parameters = array())
    {
        $query = array();
        foreach ($parameters as $name => $value) {
            $query[] = sprintf('%s=%s', $name, $value);
        }

        return implode('&', $query);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $id
     * @param string $mode
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction($id, $mode)
    {
        if (!in_array($mode, array('express', 'editor'))) {
            throw new NotFoundHttpException('Invalid mode');
        }

        $media = $this->getMedia($id);

        $provider = $this->pool->getProvider($media->getProviderName());

        $hash = $this->generateHash($media);

        $parameters = array(
            's'          => 'c', // ??
            'referrer'   => $this->referrer,
            'exit'       => $this->router->generate('sonata_media_pixlr_exit', array('hash' => $hash, 'id' => $media->getId()), true),
            'image'      => $provider->generatePublicUrl($media, 'reference'),
            'title'      => $media->getName(),
            'target'     => $this->router->generate('sonata_media_pixlr_target', array('hash' => $hash, 'id' => $media->getId()), true),
            'locktitle'  => true,
            'locktarget' => true,
        );

        $url = sprintf('http://pixlr.com/%s/?%s', $mode, $this->buildQuery($parameters));

        return new RedirectResponse($url);
    }

    /**
     * @param string $hash
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function exitAction($hash, $id)
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        return new Response($this->templating->render('SonataMediaBundle:Extra:pixlr_exit.html.twig'));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string                                    $hash
     * @param string                                    $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function targetAction(Request $request, $hash, $id)
    {
        $media = $this->getMedia($id);

        $this->checkMedia($hash, $media);

        $provider = $this->pool->getProvider($media->getProviderName());

        /**
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

        return new Response($this->templating->render('SonataMediaBundle:Extra:pixlr_exit.html.twig'));
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return bool
     */
    public function isEditable(MediaInterface $media)
    {
        if (!$this->container->get('sonata.media.admin.media')->isGranted('EDIT', $media)) {
            return false;
        }

        return in_array(strtolower($media->getExtension()), $this->validFormats);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openEditorAction($id)
    {
        $media = $this->getMedia($id);

        if (!$this->isEditable($media)) {
            throw new NotFoundHttpException('The media is not editable');
        }

        return new Response($this->templating->render('SonataMediaBundle:Extra:pixlr_editor.html.twig', array(
            'media'      => $media,
            'admin_pool' => $this->container->get('sonata.admin.pool'),
        )));
    }
}
