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

namespace Sonata\MediaBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\CoreBundle\Model\BaseEntityManager;
use Sonata\MediaBundle\Controller\MediaController;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Security\DownloadStrategyInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MediaControllerTest extends TestCase
{
    protected $container;
    protected $controller;

    protected function setUp()
    {
        $this->container = $this->prophesize(Container::class);

        $this->controller = new MediaController();
        $this->controller->setContainer($this->container->reveal());
    }

    public function testDownloadActionWithNotFoundMedia()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $request = $this->prophesize(Request::class);
        $media = $this->prophesize(Media::class);
        $pool = $this->prophesize(Pool::class);

        $this->configureGetCurrentRequest($request->reveal());
        $this->configureGetMedia(1, $media->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), false);
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionBinaryFile()
    {
        $media = $this->prophesize(Media::class);
        $pool = $this->prophesize(Pool::class);
        $provider = $this->prophesize(MediaProviderInterface::class);
        $request = $this->prophesize(Request::class);
        $response = $this->prophesize(BinaryFileResponse::class);

        $this->configureGetMedia(1, $media->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), true);
        $this->configureGetProvider($pool, $media, $provider->reveal());
        $this->configureGetCurrentRequest($request->reveal());
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());
        $pool->getDownloadMode($media->reveal())->willReturn('mode');
        $provider->getDownloadResponse($media->reveal(), 'format', 'mode')->willReturn($response->reveal());
        $response->prepare($request->reveal())->shouldBeCalled();

        $result = $this->controller->downloadAction(1, 'format');

        $this->assertSame($response->reveal(), $result);
    }

    public function testViewActionWithNotFoundMedia()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->viewAction(1);
    }

    public function testViewActionAccessDenied()
    {
        $this->expectException(AccessDeniedException::class);

        $media = $this->prophesize(Media::class);
        $pool = $this->prophesize(Pool::class);
        $request = $this->prophesize(Request::class);

        $this->configureGetMedia(1, $media->reveal());
        $this->configureGetCurrentRequest($request->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), false);
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());

        $this->controller->viewAction(1);
    }

    public function testViewActionRendersView()
    {
        $media = $this->prophesize(Media::class);
        $pool = $this->prophesize(Pool::class);
        $request = $this->prophesize(Request::class);

        $this->configureGetMedia(1, $media->reveal());
        $this->configureGetCurrentRequest($request->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), true);
        $this->configureRender('@SonataMedia/Media/view.html.twig', [
            'media' => $media->reveal(),
            'formats' => ['format'],
            'format' => 'format',
        ], 'renderResponse');
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());
        $media->getContext()->willReturn('context');
        $pool->getFormatNamesByContext('context')->willReturn(['format']);

        $response = $this->controller->viewAction(1, 'format');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('renderResponse', $response->getContent());
    }

    private function configureDownloadSecurity($pool, $media, $request, $isGranted)
    {
        $strategy = $this->prophesize(DownloadStrategyInterface::class);

        $pool->getDownloadSecurity($media)->willReturn($strategy->reveal());
        $strategy->isGranted($media, $request)->willReturn($isGranted);
    }

    private function configureGetMedia($id, $media)
    {
        $mediaManager = $this->prophesize(BaseEntityManager::class);

        $this->container->get('sonata.media.manager.media')->willReturn($mediaManager->reveal());
        $mediaManager->find($id)->willReturn($media);
    }

    private function configureGetProvider($pool, $media, $provider)
    {
        $pool->getProvider('provider')->willReturn($provider);
        $media->getProviderName()->willReturn('provider');
    }

    private function configureGetCurrentRequest($request)
    {
        $requestStack = $this->prophesize(RequestStack::class);

        $this->container->has('request_stack')->willReturn(true);
        $this->container->get('request_stack')->willReturn($requestStack->reveal());
        $requestStack->getCurrentRequest()->willReturn($request);
    }

    private function configureRender($template, $data, $rendered)
    {
        $templating = $this->prophesize(EngineInterface::class);
        $response = $this->prophesize(Response::class);
        $pool = $this->prophesize(Pool::class);

        $this->container->has('templating')->willReturn(true);
        $this->container->get('templating')->willReturn($templating->reveal());
        $response->getContent()->willReturn($rendered);
        $templating->renderResponse($template, $data, null)->willReturn($response->reveal());
        $templating->render($template, $data)->willReturn($rendered);
    }
}
