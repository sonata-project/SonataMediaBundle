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

namespace Sonata\MediaBundle\Tests\Controller\Api;

use FOS\RestBundle\Request\ParamFetcherInterface;
use PHPUnit\Framework\TestCase;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Controller\Api\GalleryController;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryControllerTest extends TestCase
{
    public function testGetGalleriesAction(): void
    {
        $pager = $this->createStub(PagerInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects($this->once())->method('getPager')->willReturn($pager);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher
            ->expects($this->once())
            ->method('all')
            ->willReturn([
                'page' => 1,
                'count' => 10,
                'orderBy' => ['id' => 'ASC'],
            ]);

        $this->assertSame($pager, $gController->getGalleriesAction($paramFetcher));
    }

    public function testGetGalleryAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame($gallery, $gController->getGalleryAction(1));
    }

    public function testGetGalleryNotFoundAction(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Gallery (42) not found');

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects($this->once())->method('findOneBy');

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $gController->getGalleryAction(42);
    }

    public function testGetGalleryGalleryItemsAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gallery->expects($this->once())->method('getGalleryItems')->willReturn([$galleryItem]);

        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$galleryItem], $gController->getGalleryGalleryItemsAction(1));
    }

    public function testGetGalleryMediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $galleryItem->expects($this->once())->method('getMedia')->willReturn($media);
        $gallery->expects($this->once())->method('getGalleryItems')->willReturn([$galleryItem]);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$media], $gController->getGalleryMediasAction(1));
    }
}
