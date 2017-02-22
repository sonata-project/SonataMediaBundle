<?php

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
use Sonata\MediaBundle\Controller\Api\GalleryController;
use Sonata\MediaBundle\Model\GalleryItem;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Request;

class GalleryTest extends GalleryItem
{
    private $id;

    public function __construct()
    {
        parent::__construct();
        $this->id = rand();
    }

    public function getId()
    {
        return $this->id;
    }
}

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryControllerTest extends PHPUnit_Framework_TestCase
{
    public function testGetGalleriesAction()
    {
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryManager->expects($this->once())->method('getPager')->will($this->returnValue(array()));

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $paramFetcher = $this->getMockBuilder('FOS\RestBundle\Request\ParamFetcher')
            ->disableOriginalConstructor()
            ->getMock();
        $paramFetcher->expects($this->exactly(3))->method('get');
        $paramFetcher
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                'page' => 1,
                'count' => 10,
                'orderBy' => array('id' => 'ASC'),
            )));

        $this->assertSame(array(), $gController->getGalleriesAction($paramFetcher));
    }

    public function testGetGalleryAction()
    {
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame($gallery, $gController->getGalleryAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Gallery (42) not found
     */
    public function testGetGalleryNotFoundAction()
    {
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryManager->expects($this->once())->method('findOneBy');

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $gController->getGalleryAction(42);
    }

    public function testGetGalleryGalleryItemsAction()
    {
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));

        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame(array($galleryItem), $gController->getGalleryGalleryItemAction(1));
    }

    public function testGetGalleryMediaAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'test');

        $this->assertSame(array($media), $gController->getGalleryMediasAction(1));
    }

    public function testPostGalleryMediaGalleryItemAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $media2 = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media2->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media2));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryItem));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->postGalleryMediaGalleryItemAction(1, 2, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPostGalleryMediaGalleryItemInvalidAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->postGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }

    public function testPutGalleryMediaGalleryItemAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryItem));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->putGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertSame(200, $view->getResponse()->getStatusCode(), 'Should return 200');
    }

    public function testPutGalleryMediaGalleryItemInvalidAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery->expects($this->once())->method('getGalleryItems')->will($this->returnValue(array($galleryItem)));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('handleRequest');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->putGalleryMediaGalleryItemAction(1, 1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeleteGalleryMediaGalleryItemAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media->expects($this->any())->method('getId')->will($this->returnValue(1));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media));
        $gallery
            ->expects($this->any())
            ->method('getGalleryItems')
            ->will($this->returnValue(new ArrayCollection(array($galleryItem))));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->deleteGalleryMediaGalleryItemAction(1, 1);

        $this->assertSame(array('deleted' => true), $view);
    }

    public function testDeleteGalleryMediaGalleryItemInvalidAction()
    {
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $media2 = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $galleryItem = $this->createMock('Sonata\MediaBundle\Model\GalleryItemInterface');
        $gallery = $this->createMock('Sonata\MediaBundle\Model\GalleryInterface');
        $galleryManager = $this->createMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->createMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $media2->expects($this->any())->method('getId')->will($this->returnValue(2));
        $galleryItem->expects($this->once())->method('getMedia')->will($this->returnValue($media2));
        $gallery
            ->expects($this->any())
            ->method('getGalleryItems')
            ->will($this->returnValue(new ArrayCollection(array($galleryItem))));
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->createMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController(
            $galleryManager,
            $mediaManager,
            $formFactory,
            'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest'
        );
        $view = $galleryController->deleteGalleryMediaGalleryItemAction(1, 1);

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertSame(400, $view->getResponse()->getStatusCode(), 'Should return 400');
    }
}
