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
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        $manager->expects($this->once())->method('findOneBy')->will($this->returnValue($media));

        $controller = $this->createMediaController($manager);

        $this->assertEquals($media, $controller->getMediaAction(1));
    }

    /**
     * @expectedException        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Media (42) was not found
     */
    public function testGetMediaNotFoundExceptionAction()
    {
        $this->createMediaController()->getMediaAction(42);
    }

    public function testGetMediaFormatsAction()
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
                'protected_url' => null,
                'properties' => array(
                    'foo' => "bar"
                ),
            ),
            'format_name1' =>array(
                'protected_url' => null,
                'properties' => array(
                    'foo' => "bar"
                ),
            ),
        );
        $this->assertEquals($expected, $controller->getMediaFormatsAction(1));
    }

    public function testGetMediaBinariesAction()
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

        $this->assertEquals($binaryResponse, $controller->getMediaBinaryAction(1, 'format', new Request()));
    }

    protected function createMediaController($manager = null, $pool = null, $router = null)
    {
        if (null === $manager) {
            $manager = $this->getMock('Sonata\MediaBundle\Model\MediaManagerInterface');
        }
        if (null === $pool) {
            $pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
        }
        if (null === $router) {
            $router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        }

        return new MediaController($manager, $pool, $router);
    }
}
