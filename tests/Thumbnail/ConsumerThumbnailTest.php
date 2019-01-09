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

namespace Sonata\MediaBundle\Tests\Thumbnail;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Thumbnail\ConsumerThumbnail;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumerThumbnailTest extends TestCase
{
    public function testGenerateDispatchesEvents(): void
    {
        $thumbnail = $this->createMock(ThumbnailInterface::class);
        $backend = $this->createMock(BackendInterface::class);
        $provider = $this->createMock(MediaProviderInterface::class);
        $media = $this->createMock(MediaInterface::class);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
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
