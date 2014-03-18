<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sonata\MediaBundle\Tests\Controller\Api;

use Doctrine\Common\Collections\ArrayCollection;

use Sonata\MediaBundle\Controller\Api\GalleryController;
use Sonata\MediaBundle\Model\GalleryHasMedia;
use Symfony\Component\HttpFoundation\Request;

class GalleryTest extends GalleryHasMedia
{

}

/**
 * Class GalleryControllerTest
 *
 * @package Sonata\MediaBundle\Tests\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class GalleryControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGalleriesAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $gManager->expects($this->once())->method('findBy')->will($this->returnValue(array($gallery)));

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $params = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $params->expects($this->once())->method('all')->will($this->returnValue(array('page' => 1, 'count' => 10, 'orderBy' => array('id' => "ASC"))));
        $params->expects($this->exactly(3))->method('get');

        $this->assertEquals(array($gallery), $gController->getGalleriesAction($params));
    }

    public function testGetGalleryAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertEquals($gallery, $gController->getGalleryAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Gallery (42) not found
     */
    public function testGetGalleryNotFoundAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $gManager->expects($this->once())->method('findOneBy');

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $gController->getGalleryAction(42);
    }

    public function testGetGalleryGalleryhasmediasAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertEquals(array($galleryHasMedia), $gController->getGalleryGalleryhasmediasAction(1));
    }

    public function testGetGalleryMediaAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');

        $gController = new GalleryController($gManager, $mediaManager, $formFactory, 'test');

        $this->assertEquals(array($media), $gController->getGalleryMediasAction(1));
    }

    public function testPostGalleryMediaGalleryhasmediaAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $media2 = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media2->expects($this->any())->method('getId')->will($this->returnValue(1));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media2));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryHasMedia));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->postGalleryMediaGalleryhasmediaAction(1, 2, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertEquals(200, $view->getStatusCode(), 'Should return 200');
    }

    public function testPostGalleryMediaGalleryhasmediaInvalidAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->postGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertEquals(400, $view->getStatusCode(), 'Should return 400');
    }

    public function testPutGalleryMediaGalleryhasmediaAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($galleryHasMedia));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->putGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertEquals(200, $view->getStatusCode(), 'Should return 200');
    }

    public function testPutGalleryMediaGalleryhasmediaInvalidAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->putGalleryMediaGalleryhasmediaAction(1, 1, new Request());

        $this->assertInstanceOf('Symfony\Component\Form\FormInterface', $view);
    }

    public function testDeleteGalleryMediaGalleryhasmediaAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getId')->will($this->returnValue(1));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->any())->method('getGalleryHasMedias')->will($this->returnValue(new ArrayCollection(array($galleryHasMedia))));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->deleteGalleryMediaGalleryhasmediaAction(1, 1);

        $this->assertEquals(array('deleted' => true), $view);
    }

    public function testDeleteGalleryMediaGalleryhasmediaInvalidAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $media2 = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media2->expects($this->any())->method('getId')->will($this->returnValue(2));

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media2));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->any())->method('getGalleryHasMedias')->will($this->returnValue(new ArrayCollection(array($galleryHasMedia))));

        $galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $mediaManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $mediaManager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $galleryController = new GalleryController($galleryManager, $mediaManager, $formFactory, 'Sonata\MediaBundle\Tests\Controller\Api\GalleryTest');
        $view = $galleryController->deleteGalleryMediaGalleryhasmediaAction(1, 1);

        $this->assertInstanceOf('FOS\RestBundle\View\View', $view);
        $this->assertEquals(400, $view->getStatusCode(), 'Should return 400');
    }
}
