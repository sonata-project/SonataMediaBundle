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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Jordi Sala Morales <jordism91@gmail.name>
 */
final class MessengerThumbnailTest extends TestCase
{
    /**
     * @var MockObject&ThumbnailInterface
     */
    private $innerThumbnail;

    /**
     * @var MockObject&MessageBusInterface
     */
    private $bus;

    /**
     * @var MessengerThumbnail
     */
    private $thumbnail;

    protected function setUp(): void
    {
        $this->innerThumbnail = $this->createMock(ThumbnailInterface::class);
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->thumbnail = new MessengerThumbnail($this->innerThumbnail, $this->bus);
    }

    public function testGeneratePublicUrl(): void
    {
        $this->innerThumbnail->expects(static::once())->method('generatePublicUrl')->willReturn('public_url');

        $publicUrl = $this->thumbnail->generatePublicUrl(
            $this->createStub(MediaProviderInterface::class),
            new Media(),
            'format'
        );

        static::assertSame('public_url', $publicUrl);
    }

    public function testGeneratePrivateUrl(): void
    {
        $this->innerThumbnail->expects(static::once())->method('generatePrivateUrl')->willReturn('private_url');

        $publicUrl = $this->thumbnail->generatePrivateUrl(
            $this->createStub(MediaProviderInterface::class),
            new Media(),
            'format'
        );

        static::assertSame('private_url', $publicUrl);
    }

    public function testGenerateThumbnailsWithoutId(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot generate thumbnails for media without id.');

        $this->thumbnail->generate(
            $this->createStub(MediaProviderInterface::class),
            new Media()
        );
    }

    public function testGenerateThumbnails(): void
    {
        $media = new Media();
        $media->setId(25);

        $this->bus->expects(static::once())->method('dispatch')->willReturn(new Envelope(new \stdClass()));

        $this->thumbnail->generate(
            $this->createStub(MediaProviderInterface::class),
            $media
        );
    }

    public function testDeleteThumbnails(): void
    {
        $this->innerThumbnail->expects(static::once())->method('delete')->willReturn('private_url');

        $publicUrl = $this->thumbnail->delete(
            $this->createStub(MediaProviderInterface::class),
            new Media(),
            'format'
        );

        static::assertSame('private_url', $publicUrl);
    }
}
