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

use Doctrine\Common\Collections\ArrayCollection;
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
 * NEXT_MAJOR: Remove this class.
 *
 * @author Hugo Briand <briand@ekino.com>
 *
 * @group legacy
 */
class GalleryControllerTest extends TestCase
{
    public function testGetGalleriesAction(): void
    {
        $pager = $this->createStub(PagerInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects(static::once())->method('getPager')->willReturn($pager);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory);

        $paramFetcher = $this->createMock(ParamFetcherInterface::class);
        $paramFetcher->expects(static::exactly(3))->method('get');
        $paramFetcher
            ->expects(static::once())
            ->method('all')
            ->willReturn([
                'page' => 1,
                'count' => 10,
                'orderBy' => ['id' => 'ASC'],
            ]);

        static::assertSame($pager, $gController->getGalleriesAction($paramFetcher));
    }

    public function testGetGalleryAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects(static::once())->method('findOneBy')->willReturn($gallery);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory);

        static::assertSame($gallery, $gController->getGalleryAction(1));
    }

    /**
     * @dataProvider getIdsForNotFound
     *
     * @param int|string $identifier
     */
    public function testGetGalleryNotFoundAction($identifier, string $message): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage($message);

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects(static::once())->method('findOneBy');

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory);

        $gController->getGalleryAction($identifier);
    }

    /**
     * @phpstan-return iterable<array{int|string, string}>
     */
    public function getIdsForNotFound(): iterable
    {
        yield [42, 'Gallery not found for identifier 42.'];
        yield ['42', 'Gallery not found for identifier \'42\'.'];
        yield ['', 'Gallery not found for identifier \'\'.'];
    }

    public function testGetGalleryGalleryItemsAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gallery->expects(static::once())->method('getGalleryItems')->willReturn(new ArrayCollection([$galleryItem]));

        $galleryManager->expects(static::once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory);

        static::assertSame([$galleryItem], $gController->getGalleryGalleryItemsAction(1)->toArray());
    }

    public function testGetGalleryMediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $galleryItem->expects(static::once())->method('getMedia')->willReturn($media);
        $gallery->expects(static::once())->method('getGalleryItems')->willReturn(new ArrayCollection([$galleryItem]));
        $galleryManager->expects(static::once())->method('findOneBy')->willReturn($gallery);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory);

        static::assertSame([$media], $gController->getGalleryMediasAction(1));
    }
}
