<?php

namespace Sonata\MediaBundle\Tests\Security;

use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;

class LiipImagineThumbnailTest extends \PHPUnit_Framework_TestCase
{
    public function testGeneratePublicUrlDoesNotResolveCdnPath()
    {
        $router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $router->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/uploads/image.jpg'));

        $sut = new LiipImagineThumbnail($router);

        $provider = $this->getMock('\Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->never())
            ->method('getCdnPath');
        $media = $this->getMock('\Sonata\MediaBundle\Model\MediaInterface');
        $path = $sut->generatePublicUrl($provider, $media, 'default');

        $this->assertFalse($path[0] == '/');
    }
}
