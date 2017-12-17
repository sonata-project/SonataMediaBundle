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
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Controller\Api\GalleryController;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItem;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function testPostGalleryMediaGalleryItemAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $media2 = $this->createMock(MediaInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media2->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media2));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryItem));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->postGalleryMediaGalleryItemAction(1, 2, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPostGalleryMediaGalleryItemInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->postGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }

    public function testPutGalleryMediaGalleryItemAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryItem));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->putGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPutGalleryMediaGalleryItemInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue([$galleryItem]));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->putGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteGalleryMediaGalleryItemAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery
            ->expects($this->any())
            ->method('getGalleryItems')
            ->will($this->returnValue(new ArrayCollection([$galleryItem])));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->deleteGalleryMediaGalleryItemAction(1, 1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeleteGalleryMediaGalleryItemInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media2 = $this->createMock(MediaInterface::class);
        $galleryItem = $this->createMock(GalleryItemInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $media2->expects($this->any())->method('getId')->will($this->returnValue(2));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media2));
        $gallery
            ->expects($this->any())
            ->method('getGalleryItems')
            ->will($this->returnValue(new ArrayCollection([$galleryItem])));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            GalleryTest::class
        );
        $view = $galleryController->deleteGalleryMediaGalleryItemAction(1, 1);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }
}
