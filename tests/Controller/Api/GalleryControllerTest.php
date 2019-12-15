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
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryControllerTest extends TestCase
{
    public function testGetGalleriesAction(): void
    {
        $gManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gManager->expects($this->once())->method('getPager')->willReturn([]);

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $paramFetcher = $this->createMock(ParamFetcher::class);
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher->expects($this->once())->method('all')->willReturn([]);

        $this->assertSame([], $gController->getGalleriesAction($paramFetcher));
    }

    public function testGetGalleryAction(): void
    {
        $gManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertSame($gallery, $gController->getGalleryAction(1));
    }

    public function testGetGalleryNotFoundAction(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Gallery (42) not found');

        $gManager = $this->createMock(GalleryManagerInterface::class);
        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gManager->expects($this->once())->method('findOneBy');

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $gController->getGalleryAction(42);
    }

    public function testGetGalleryGalleryhasmediasAction(): void
    {
        $gManager = $this->createMock(GalleryManagerInterface::class);
        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $gallery = $this->createMock(GalleryInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $gManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$galleryHasMedia], $gController->getGalleryGalleryhasmediasAction(1));
    }

    public function testGetGalleryMediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $gManager = $this->createMock(GalleryManagerInterface::class);
        $gManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertSame([$media], $gController->getGalleryMediasAction(1));
    }

    /**
     * @group legacy
     */
    public function testPostGalleryMediaGalleryhasmediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $media2 = $this->createMock(MediaInterface::class);
        $media2->method('getId')->willReturn(1);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media2);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($galleryHasMedia);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->postGalleryMediaGalleryhasmediaAction(1, 2, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPostGalleryMediaGalleryhasmediaInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(1);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->postGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }

    /**
     * @group legacy
     */
    public function testPutGalleryMediaGalleryhasmediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(1);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(true);
        $form->expects($this->once())->method('getData')->willReturn($galleryHasMedia);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->putGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPutGalleryMediaGalleryhasmediaInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(1);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->expects($this->once())->method('getGalleryHasMedias')->willReturn([$galleryHasMedia]);

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $form = $this->createMock(Form::class);
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->expects($this->once())->method('createNamed')->willReturn($form);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->putGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf(FormInterface::class, $view);
    }

    public function testDeleteGalleryMediaGalleryhasmediaAction(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $media->method('getId')->willReturn(1);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->method('getGalleryHasMedias')->willReturn(new ArrayCollection([$galleryHasMedia]));

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->deleteGalleryMediaGalleryhasmediaAction(1, 1);

        $this->assertSame(['deleted' => true], $view);
    }

    public function testDeleteGalleryMediaGalleryhasmediaInvalidAction(): void
    {
        $media = $this->createMock(MediaInterface::class);

        $media2 = $this->createMock(MediaInterface::class);
        $media2->method('getId')->willReturn(2);

        $galleryHasMedia = $this->createMock(GalleryHasMediaInterface::class);
        $galleryHasMedia->expects($this->once())->method('getMedia')->willReturn($media2);

        $gallery = $this->createMock(GalleryInterface::class);
        $gallery->method('getGalleryHasMedias')->willReturn(new ArrayCollection([$galleryHasMedia]));

        $galleryManager = $this->createMock(GalleryManagerInterface::class);
        $galleryManager->expects($this->once())->method('findOneBy')->willReturn($gallery);

        $mediaManager = $this->createMock(MediaManagerInterface::class);
        $mediaManager->expects($this->once())->method('findOneBy')->willReturn($media);

        $formFactory = $this->createMock(FormFactoryInterface::class);

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, GalleryTest::class);
        $view = $galleryController->deleteGalleryMediaGalleryhasmediaAction(1, 1);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }
}
