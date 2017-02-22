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

use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\MediaBundle\Thumbnail\ConsumerThumbnail;

class ConsumerThumbnailTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateDispatchesEvents()
    {
        $thumbnail = $this->createMock('Sonata\MediaBundle\Thumbnail\ThumbnailInterface');
        $backend = $this->createMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $provider = $this->createMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');

        $dispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
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
