<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Thumbnail;

use Sonata\MediaBundle\Thumbnail\ConsumerThumbnail;

class ConsumerThumbnailTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateDispatchesEvents()
    {
        $thumbnail = $this->getMock('Sonata\MediaBundle\Thumbnail\ThumbnailInterface');
        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->at(0))
            ->method('addListener')
            ->with($this->equalTo('kernel.finish_request'), $this->anything());

        $dispatcher->expects($this->at(1))
            ->method('addListener')
            ->with($this->equalTo('console.terminate'), $this->anything());

        $consumer = new ConsumerThumbnail('foo', $thumbnail, $backend, $dispatcher);
        $consumer->generate($provider, $media);
    }
}
