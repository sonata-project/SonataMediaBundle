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
use PHPUnit\Framework\MockObject\Stub;
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

class MediaControllerTest extends TestCase
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var MockObject&MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var MediaController
     */
    private $controller;

    /**
     * @psalm-suppress InternalMethod
     *
     * @see https://github.com/symfony/symfony/issues/42052
     */
    protected function setUp(): void
    {
        $this->pool = new Pool('default');
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->container = new Container();

        $this->controller = new MediaController($this->mediaManager, $this->pool);
        $this->controller->setContainer($this->container);
    }

    public function testDownloadActionWithNotFoundMedia(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->downloadAction(new Request(), 1);
    }

    public function testDownloadActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $request = $this->createStub(Request::class);
        $media = $this->createStub(Media::class);
        $media->method('getContext')->willReturn('context');

        $this->configureGetCurrentRequest($request);
        $this->configureGetMedia(1, $media);
        $this->configureDownloadSecurity($media, $request, false);

        $this->controller->downloadAction($request, 1);
    }

    public function testDownloadActionBinaryFile(): void
    {
        $media = $this->createStub(Media::class);
        $provider = $this->createMock(MediaProviderInterface::class);
        $request = $this->createStub(Request::class);
        $response = $this->createMock(BinaryFileResponse::class);

        $this->configureGetMedia(1, $media);
        $this->configureDownloadSecurity($media, $request, true);
        $this->configureGetProvider($media, $provider);
        $this->configureGetCurrentRequest($request);

        $media->method('getContext')->willReturn('context');
        $provider->method('getDownloadResponse')->with($media, 'format', 'mode')->willReturn($response);
        $response->expects(static::once())->method('prepare')->with($request);

        $result = $this->controller->downloadAction($request, 1, 'format');

        static::assertSame($response, $result);
    }

    public function testViewActionWithNotFoundMedia(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->viewAction(new Request(), 1);
    }

    public function testViewActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $media = $this->createStub(Media::class);
        $request = $this->createStub(Request::class);

        $media->method('getContext')->willReturn('context');

        $this->configureGetMedia(1, $media);
        $this->configureGetCurrentRequest($request);
        $this->configureDownloadSecurity($media, $request, false);

        $this->controller->viewAction($request, 1);
    }

    public function testViewActionRendersView(): void
    {
        $media = $this->createStub(Media::class);
        $request = $this->createStub(Request::class);

        $this->configureGetMedia(1, $media);
        $this->configureGetCurrentRequest($request);
        $this->configureDownloadSecurity($media, $request, true);
        $this->configureRender('@SonataMedia/Media/view.html.twig', [
            'media' => $media,
            'formats' => ['format' => [
                'width' => null,
                'height' => null,
                'quality' => 80,
                'format' => 'jpg',
                'constraint' => false,
                'resizer' => null,
                'resizer_options' => [],
            ]],
            'format' => 'format',
        ], 'renderResponse');

        $media->method('getContext')->willReturn('context');

        $response = $this->controller->viewAction($request, 1, 'format');

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
    }

    /**
     * @param Stub&Media   $media
     * @param Stub&Request $request
     */
    private function configureDownloadSecurity(
        object $media,
        object $request,
        bool $isGranted
    ): void {
        $strategy = $this->createMock(DownloadStrategyInterface::class);

        $this->pool->addContext('context', [], ['format' => [
            'width' => null,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => false,
            'resizer' => null,
            'resizer_options' => [],
        ]], [
            'mode' => 'mode',
            'strategy' => 'download',
        ]);
        $this->pool->addDownloadStrategy('download', $strategy);

        $strategy->method('isGranted')->with($media, $request)->willReturn($isGranted);
    }

    private function configureGetMedia(int $id, ?Media $media): void
    {
        $this->mediaManager->method('find')->with($id)->willReturn($media);
    }

    /**
     * @param Stub&Media $media
     */
    private function configureGetProvider(
        object $media,
        MediaProviderInterface $provider
    ): void {
        $this->pool->addProvider('provider', $provider);
        $media->method('getProviderName')->willReturn('provider');
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $this->container->set('request_stack', $requestStack);
        $requestStack->method('getCurrentRequest')->willReturn($request);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function configureRender(
        string $template,
        array $data,
        string $rendered
    ): void {
        $twig = $this->createMock(Environment::class);
        $response = $this->createStub(Response::class);

        $this->container->set('twig', $twig);
        $response->method('getContent')->willReturn($rendered);
        $twig->method('render')->with($template, $data)->willReturn($rendered);
    }
}
