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

    protected function createMediaController($manager = null, $pool = null)
    {
        if (null === $manager) {
            $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        }
        if (null === $pool) {
            $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        }

        return new MediaController($manager, $pool);
    }
}
