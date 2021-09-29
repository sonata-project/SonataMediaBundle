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

/**
 * NEXT_MAJOR: Remove this class.
 *
 * @group legacy
 */
class ConsumerThumbnailTest extends TestCase
{
    public function testGenerateDispatchesEvents(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(static::exactly(2))
            ->method('addListener')
            ->withConsecutive(
                ['kernel.finish_request', static::anything()],
                ['console.terminate', static::anything()]
            );

        $consumer = new ConsumerThumbnail(
            'foo',
            $this->createStub(ThumbnailInterface::class),
            $this->createStub(BackendInterface::class),
            $dispatcher
        );
        $consumer->generate(
            $this->createStub(MediaProviderInterface::class),
            $this->createStub(MediaInterface::class)
        );
    }
}
