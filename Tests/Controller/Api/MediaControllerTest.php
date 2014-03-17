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

use Sonata\MediaBundle\Controller\Api\MediaController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MediaControllerTest
 *
 * @package Sonata\MediaBundle\Tests\Controller\Api
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MediaControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetMediaAction()
    {
        $mManager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $media    = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $mManager->expects($this->once())->method('findBy')->will($this->returnValue(array($media)));

        $mController = $this->createMediaController($mManager);

        $params = $this->getMock('FOS\RestBundle\Request\ParamFetcherInterface');
        $params->expects($this->once())->method('all')->will($this->returnValue(array('page' => 1, 'count' => 10, 'orderBy' => array('id' => "ASC"))));
        $params->expects($this->exactly(3))->method('get');

        $this->assertEquals(array($media), $mController->getMediaAction($params));
    }

    public function testGetMediumAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $controller = $this->createMediaController($manager);

        $this->assertEquals($media, $controller->getMediumAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Media (42) was not found
     */
    public function testGetMediumNotFoundExceptionAction()
    {
        $this->createMediaController()->getMediumAction(42);
    }

    public function testGetMediumFormatsAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->exactly(2))->method('getHelperProperties')->will($this->returnValue(array('foo' => 'bar')));

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->any())->method('getProvider')->will($this->returnValue($provider));
        $pool->expects($this->once())->method('getFormatNamesByContext')->will($this->returnValue(array('format_name1' => "value1")));

        $controller = $this->createMediaController($manager, $pool);

        $expected = array(
            'reference' => array(
                'properties' => array(
                    'foo' => "bar"
                ),
                'url' => null,
            ),
            'format_name1' =>array(
                'properties' => array(
                    'foo' => "bar"
                ),
                'url' => null,
            ),
        );
        $this->assertEquals($expected, $controller->getMediumFormatsAction(1));
    }

    public function testGetMediumBinariesAction()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $binaryResponse = $this->getMockBuilder('Symfony\Component\HttpFoundation\BinaryFileResponse')->disableOriginalConstructor()->getMock();

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('getDownloadResponse')->will($this->returnValue($binaryResponse));

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $controller = $this->createMediaController($manager, $pool);

        $this->assertEquals($binaryResponse, $controller->getMediumBinaryAction(1, 'format', new Request()));
    }

    public function testDeleteMediumAction()
    {
        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('delete');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($this->getMock('Sonata\MediaBundle\Model\MediaInterface')));

        $controller = $this->createMediaController($manager);

        $expected = array('deleted' => true);

        $this->assertEquals($expected, $controller->deleteMediumAction(1));
    }

    public function testPutMediumAction()
    {
        $medium = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($medium));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('getName');

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($medium));

        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf('FOS\RestBundle\View\View', $controller->putMediumAction(1, new Request()));
    }

    public function testPutMediumInvalidFormAction()
    {
        $medium = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($medium));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('getName');

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(false));

        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf('Symfony\Component\Form\Form', $controller->putMediumAction(1, new Request()));
    }

    public function testPostProviderMediumAction()
    {
        $medium = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('create')->will($this->returnValue($medium));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('getName');

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->once())->method('getProvider')->will($this->returnValue($provider));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('bind');
        $form->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $form->expects($this->once())->method('getData')->will($this->returnValue($medium));

        $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $factory->expects($this->once())->method('createNamed')->will($this->returnValue($form));

        $controller = $this->createMediaController($manager, $pool, $factory);

        $this->assertInstanceOf('FOS\RestBundle\View\View', $controller->postProviderMediumAction("providerName", new Request()));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testPostProviderActionNotFound()
    {
        $medium = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $medium->expects($this->once())->method('setProviderName');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('create')->will($this->returnValue($medium));

        $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        $pool->expects($this->once())->method('getProvider')->will($this->throwException(new \RuntimeException("exception on getProvder")));

        $controller = $this->createMediaController($manager, $pool);
        $controller->postProviderMediumAction("non existing provider", new Request());
    }

    protected function createMediaController($manager = null, $pool = null, $factory = null)
    {
        if (null === $manager) {
            $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        }
        if (null === $pool) {
            $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        }
        if (null === $factory) {
            $factory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        }

        return new MediaController($manager, $pool, $factory);
    }
}
