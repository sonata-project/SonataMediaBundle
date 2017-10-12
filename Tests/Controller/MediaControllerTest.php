<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Controller;

use Sonata\MediaBundle\Controller\MediaController;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class MediaControllerTest extends PHPUnit_Framework_TestCase
{
    protected $container;
    protected $controller;

    protected function setUp()
    {
        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\Container');

        $this->controller = new MediaController();
        $this->controller->setContainer($this->container->reveal());
    }

    public function testDownloadActionWithNotFoundMedia()
    {
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->configureGetMedia(1, null);

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionAccessDenied()
    {
        $this->expectException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $media = $this->prophesize('Sonata\MediaBundle\Model\Media');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');

        $this->configureGetCurrentRequest($request->reveal());
        $this->configureGetMedia(1, $media->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), false);
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionBinaryFile()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\Media');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $provider = $this->prophesize('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');
        $response = $this->prophesize('Symfony\Component\HttpFoundation\BinaryFileResponse');

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
        $this->expectException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');

        $this->configureGetMedia(1, null);

        $this->controller->viewAction(1);
    }

    public function testViewActionAccessDenied()
    {
        $this->expectException('Symfony\Component\Security\Core\Exception\AccessDeniedException');

        $media = $this->prophesize('Sonata\MediaBundle\Model\Media');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');

        $this->configureGetMedia(1, $media->reveal());
        $this->configureGetCurrentRequest($request->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), false);
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());

        $this->controller->viewAction(1);
    }

    public function testViewActionRendersView()
    {
        $media = $this->prophesize('Sonata\MediaBundle\Model\Media');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $request = $this->prophesize('Symfony\Component\HttpFoundation\Request');

        $this->configureGetMedia(1, $media->reveal());
        $this->configureGetCurrentRequest($request->reveal());
        $this->configureDownloadSecurity($pool, $media->reveal(), $request->reveal(), true);
        $this->configureRender('SonataMediaBundle:Media:view.html.twig', array(
            'media' => $media->reveal(),
            'formats' => array('format'),
            'format' => 'format',
        ), 'renderResponse');
        $this->container->get('sonata.media.pool')->willReturn($pool->reveal());
        $media->getContext()->willReturn('context');
        $pool->getFormatNamesByContext('context')->willReturn(array('format'));

        $response = $this->controller->viewAction(1, 'format');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('renderResponse', $response->getContent());
    }

    private function configureDownloadSecurity($pool, $media, $request, $isGranted)
    {
        $strategy = $this->prophesize('Sonata\MediaBundle\Security\DownloadStrategyInterface');

        $pool->getDownloadSecurity($media)->willReturn($strategy->reveal());
        $strategy->isGranted($media, $request)->willReturn($isGranted);
    }

    private function configureGetMedia($id, $media)
    {
        $mediaManager = $this->prophesize('Sonata\CoreBundle\Model\BaseEntityManager');

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
        // NEXT_MAJOR: Remove this trick when bumping Symfony requirement to 2.8+.
        if (class_exists('Symfony\Component\HttpFoundation\RequestStack')) {
            $requestStack = $this->prophesize('Symfony\Component\HttpFoundation\RequestStack');

            $this->container->has('request_stack')->willReturn(true);
            $this->container->get('request_stack')->willReturn($requestStack->reveal());
            $requestStack->getCurrentRequest()->willReturn($request);
        } else {
            $this->container->has('request_stack')->willReturn(false);
            $this->container->get('request')->willReturn($request);
        }
    }

    private function configureRender($template, $data, $rendered)
    {
        $templating = $this->prophesize('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $response = $this->prophesize('Symfony\Component\HttpFoundation\Response');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');

        $this->container->has('templating')->willReturn(true);
        $this->container->get('templating')->willReturn($templating->reveal());
        $response->getContent()->willReturn($rendered);
        $templating->renderResponse($template, $data, null)->willReturn($response->reveal());
        $templating->render($template, $data)->willReturn($rendered);
    }
}
