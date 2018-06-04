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

use FOS\RestBundle\Request\ParamFetcher;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Controller\Api\GalleryController;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItem;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GalleryTest extends GalleryItem
{
    private $id;

    public function __construct()
    {
        parent::__construct();
        $this->id = random_int(0, getrandmax());
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryControllerTest extends TestCase
{
    public function testGetGalleriesAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects($this->once())->method('getPager')->will($this->returnValue([]));

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $paramFetcher = $this->createMock(ParamFetcher::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                'page' => 1,
                'count' => 10,
                'orderBy' => ['id' => 'ASC'],
            ]));

        $this->assertSame([], $gController->getGalleriesAction($paramFetcher));
    }

    public function testGetGalleryAction(): void
    {
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

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

        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));

        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$galleryItem], $gController->getGalleryGalleryItemAction(1));
    }

    public function testGetGalleryMediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$media], $gController->getGalleryMediasAction(1));
    }
}
