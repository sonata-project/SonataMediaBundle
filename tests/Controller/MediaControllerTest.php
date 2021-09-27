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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Controller\MediaController;
use Sonata\MediaBundle\Model\Media;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Security\DownloadStrategyInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

/**
 * NEXT_MAJOR: Remove this class.
 */
class MediaControllerTest extends TestCase
{
    /**
     * @var MockObject&Pool
     */
    protected $pool;

    /**
     * @var MockObject&MediaManagerInterface
     */
    protected $mediaManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var MockObject&Environment
     */
    protected $twig;

    /**
     * @var MediaController
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->pool = $this->createMock(Pool::class);
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->request = new Request();
        $this->twig = $this->createMock(Environment::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $container = new Container();
        $container->set('sonata.media.pool', $this->pool);
        $container->set('sonata.media.manager.media', $this->mediaManager);
        $container->set('request_stack', $requestStack);
        $container->set('twig', $this->twig);

        $this->controller = new MediaController();
        $this->controller->setContainer($container);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testDownloadActionBinaryFile(): void
    {
        $media = $this->createStub(Media::class);
        $provider = $this->createMock(MediaProviderInterface::class);
        $response = $this->createMock(BinaryFileResponse::class);

        $this->configureGetMedia(1, $media);
        $this->configureDownloadStrategy($media, true);
        $this->configureGetProvider($media, $provider);
        $this->pool->method('getDownloadMode')->with($media)->willReturn('mode');
        $provider->method('getDownloadResponse')->with($media, 'format', 'mode')->willReturn($response);
        $response->expects(static::once())->method('prepare')->with($this->request);

        $result = $this->controller->downloadAction(1, 'format');

        static::assertSame($response, $result);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testViewActionWithNotFoundMedia(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->viewAction(1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testViewActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $media = $this->createStub(Media::class);

        $this->configureGetMedia(1, $media);
        $this->configureDownloadStrategy($media, false);

        $this->controller->viewAction(1);
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testViewActionRendersView(): void
    {
        $media = $this->createStub(Media::class);

        $this->configureGetMedia(1, $media);
        $this->configureDownloadStrategy($media, true);
        $this->configureRender('@SonataMedia/Media/view.html.twig', [
            'media' => $media,
            'formats' => ['format'],
            'format' => 'format',
        ], 'renderResponse');
        $media->method('getContext')->willReturn('context');
        $this->pool->method('getFormatNamesByContext')->with('context')->willReturn(['format']);

        $response = $this->controller->viewAction(1, 'format');

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
    }

    private function configureDownloadStrategy(
        Media $media,
        bool $isGranted
    ): void {
        $strategy = $this->createMock(DownloadStrategyInterface::class);

        $this->pool->method('getDownloadStrategy')->with($media)->willReturn($strategy);
        $this->pool->method('getDownloadSecurity')->with($media)->willReturn($strategy);
        $strategy->method('isGranted')->with($media, $this->request)->willReturn($isGranted);
    }

    private function configureGetMedia(int $id, ?Media $media): void
    {
        $this->mediaManager->method('find')->with($id)->willReturn($media);
    }

    private function configureGetProvider(
        MockObject $media,
        MediaProviderInterface $provider
    ): void {
        $this->pool->method('getProvider')->with('provider')->willReturn($provider);
        $media->method('getProviderName')->willReturn('provider');
    }

    private function configureRender(
        string $template,
        array $data,
        string $rendered
    ): void {
        $response = $this->createStub(Response::class);

        $response->method('getContent')->willReturn($rendered);
        $this->twig->method('render')->with($template, $data)->willReturn($rendered);
    }
}
