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
use Sonata\MediaBundle\Action\MediaDownloadAction;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Security\DownloadStrategyInterface;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class MediaDownloadActionTest extends TestCase
{
    /**
     * @var MockObject&MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var MediaDownloadAction
     */
    private $mediaDownloadAction;

    protected function setUp(): void
    {
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->pool = new Pool('default_context');

        $this->mediaDownloadAction = new MediaDownloadAction($this->mediaManager, $this->pool);
    }

    public function testDownloadActionWithNotFoundMedia(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->mediaDownloadAction->__invoke(new Request(), 1);
    }

    public function testDownloadActionAccessDenied(): void
    {
        $this->expectException(AccessDeniedException::class);

        $request = new Request();
        $media = new Media();
        $media->setContext('default_context');

        $this->configureDownloadSecurity($media, $request, false);
        $this->mediaManager->method('find')->with(1)->willReturn($media);

        $this->mediaDownloadAction->__invoke($request, 1);
    }

    public function testDownloadActionBinaryFile(): void
    {
        $media = new Media();
        $media->setContext('default_context');
        $media->setProviderName('provider');

        $request = new Request();

        $provider = $this->createMock(MediaProviderInterface::class);
        $response = $this->createMock(BinaryFileResponse::class);

        $this->configureDownloadSecurity($media, $request, true);

        $this->pool->addProvider('provider', $provider);
        $this->mediaManager->method('find')->with(1)->willReturn($media);
        $provider->method('getDownloadResponse')->with($media, 'format', 'mode')->willReturn($response);
        $response->expects(static::once())->method('prepare')->with($request);

        $result = $this->mediaDownloadAction->__invoke($request, 1, 'format');

        static::assertSame($response, $result);
    }

    private function configureDownloadSecurity(
        Media $media,
        Request $request,
        bool $isGranted
    ): void {
        $strategy = $this->createMock(DownloadStrategyInterface::class);
        $strategy->method('isGranted')->with($media, $request)->willReturn($isGranted);

        $this->pool->addContext('default_context', [], [], [
            'mode' => 'mode',
            'strategy' => 'download_strategy',
        ]);
        $this->pool->addDownloadStrategy('download_strategy', $strategy);
    }
}
