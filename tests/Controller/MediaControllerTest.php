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
use Sonata\Doctrine\Entity\BaseEntityManager;
use Sonata\MediaBundle\Controller\MediaController;
use Sonata\MediaBundle\Model\Media;
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
     * @var Container
     */
    protected $container;

    /**
     * @var MediaController
     */
    protected $controller;

    protected function setUp(): void
    {
        $this->container = new Container();

        $this->controller = new MediaController();
        $this->controller->setContainer($this->container);
    }

    public function testDownloadActionWithNotFoundMedia(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->configureGetMedia(1, null);

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $request = $this->createStub(Request::class);
        $media = $this->createStub(Media::class);
        $pool = $this->createStub(Pool::class);

        $this->configureGetCurrentRequest($request);
        $this->configureGetMedia(1, $media);
        $this->configureDownloadSecurity($pool, $media, $request, false);
        $this->container->set('sonata.media.pool', $pool);

        $this->controller->downloadAction(1);
    }

    public function testDownloadActionBinaryFile(): void
    {
        $media = $this->createStub(Media::class);
        $pool = $this->createStub(Pool::class);
        $provider = $this->createStub(MediaProviderInterface::class);
        $request = $this->createStub(Request::class);
        $response = $this->createMock(BinaryFileResponse::class);

        $this->configureGetMedia(1, $media);
        $this->configureDownloadSecurity($pool, $media, $request, true);
        $this->configureGetProvider($pool, $media, $provider);
        $this->configureGetCurrentRequest($request);
        $this->container->set('sonata.media.pool', $pool);
        $pool->method('getDownloadMode')->with($media)->willReturn('mode');
        $provider->method('getDownloadResponse')->with($media, 'format', 'mode')->willReturn($response);
        $response->expects(static::once())->method('prepare')->with($request);

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
        $pool = $this->createStub(Pool::class);
        $request = $this->createStub(Request::class);

        $this->configureGetMedia(1, $media);
        $this->configureGetCurrentRequest($request);
        $this->configureDownloadSecurity($pool, $media, $request, false);
        $this->container->set('sonata.media.pool', $pool);

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
        $pool = $this->createStub(Pool::class);
        $request = $this->createStub(Request::class);

        $this->configureGetMedia(1, $media);
        $this->configureGetCurrentRequest($request);
        $this->configureDownloadSecurity($pool, $media, $request, true);
        $this->configureRender('@SonataMedia/Media/view.html.twig', [
            'media' => $media,
            'formats' => ['format'],
            'format' => 'format',
        ], 'renderResponse');
        $this->container->set('sonata.media.pool', $pool);
        $media->method('getContext')->willReturn('context');
        $pool->method('getFormatNamesByContext')->with('context')->willReturn(['format']);

        $response = $this->controller->viewAction(1, 'format');

        static::assertInstanceOf(Response::class, $response);
        static::assertSame('renderResponse', $response->getContent());
    }

    private function configureDownloadSecurity(
        MockObject $pool,
        Media $media,
        Request $request,
        bool $isGranted
    ): void {
        $strategy = $this->createStub(DownloadStrategyInterface::class);

        $pool->method('getDownloadSecurity')->with($media)->willReturn($strategy);
        $strategy->method('isGranted')->with($media, $request)->willReturn($isGranted);
    }

    private function configureGetMedia(int $id, ?Media $media): void
    {
        $mediaManager = $this->createStub(BaseEntityManager::class);

        $this->container->set('sonata.media.manager.media', $mediaManager);
        $mediaManager->method('find')->with($id)->willReturn($media);
    }

    private function configureGetProvider(
        MockObject $pool,
        MockObject $media,
        MediaProviderInterface $provider
    ): void {
        $pool->method('getProvider')->with('provider')->willReturn($provider);
        $media->method('getProviderName')->willReturn('provider');
    }

    private function configureGetCurrentRequest(Request $request): void
    {
        $requestStack = $this->createStub(RequestStack::class);

        $this->container->set('request_stack', $requestStack);
        $requestStack->method('getCurrentRequest')->willReturn($request);
    }

    private function configureRender(
        string $template,
        array $data,
        string $rendered
    ): void {
        $twig = $this->createStub(Environment::class);
        $response = $this->createStub(Response::class);

        $this->container->set('twig', $twig);
        $response->method('getContent')->willReturn($rendered);
        $twig->method('render')->with($template, $data)->willReturn($rendered);
    }
}
