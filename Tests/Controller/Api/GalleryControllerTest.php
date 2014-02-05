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

use Sonata\MediaBundle\Controller\Api\GalleryController;


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
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');

        $gManager->expects($this->once())->method('findBy')->will($this->returnValue(array($gallery)));

        $gController = new GalleryController($gManager);

        $params = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $params->expects($this->once())->method('all')->will($this->returnValue(array('page' => 1, 'count' => 10, 'orderBy' => array('id' => "ASC"))));
        $params->expects($this->exactly(3))->method('get');

        $this->assertEquals(array($gallery), $gController->getGalleriesAction($params));
    }

    public function testGetGalleryAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');

        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($gManager);

        $this->assertEquals($gallery, $gController->getGalleryAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Gallery (42) not found
     */
    public function testGetGalleryNotFoundAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $gManager->expects($this->once())->method('findOneBy');

        $gController = new GalleryController($gManager);

        $gController->getGalleryAction(42);
    }

    public function testGetGalleryGalleryhasmediasAction()
    {
        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($gManager);

        $this->assertEquals(array($galleryHasMedia), $gController->getGalleryGalleryhasmediasAction(1));
    }

    public function testGetGalleryMediaAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $galleryHasMedia = $this->getMock('Sonata\MediaBundle\Model\GalleryHasMediaInterface');
        $galleryHasMedia->expects($this->once())->method('getMedia')->will($this->returnValue($media));

        $gallery = $this->getMock('Sonata\MediaBundle\Model\GalleryInterface');
        $gallery->expects($this->once())->method('getGalleryHasMedias')->will($this->returnValue(array($galleryHasMedia)));

        $gManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $gManager->expects($this->once())->method('findOneBy')->will($this->returnValue($gallery));

        $gController = new GalleryController($gManager);

        $this->assertEquals(array($media), $gController->getGalleryMediasAction(1));
    }
}
