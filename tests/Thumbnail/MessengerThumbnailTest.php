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
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Thumbnail\MessengerThumbnail;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author Jordi Sala Morales <jordism91@gmail.name>
 */
final class MessengerThumbnailTest extends TestCase
{
    private FormatThumbnail $innerThumbnail;

    /**
     * @var MockObject&MessageBusInterface
     */
    private MessageBusInterface $bus;

    private MessengerThumbnail $thumbnail;

    protected function setUp(): void
    {
        $this->innerThumbnail = new FormatThumbnail('foo');
        $this->bus = $this->createMock(MessageBusInterface::class);

        $this->thumbnail = new MessengerThumbnail($this->innerThumbnail, $this->bus);
    }

    public function testGeneratePublicUrl(): void
    {
        $media = new Media();
        $media->setId(25);

        $publicUrl = $this->thumbnail->generatePublicUrl(
            $this->createStub(MediaProviderInterface::class),
            $media,
            'format'
        );

        static::assertSame('/thumb_25_format.foo', $publicUrl);
    }

    public function testGeneratePrivateUrl(): void
    {
        $media = new Media();
        $media->setId(25);

        $publicUrl = $this->thumbnail->generatePrivateUrl(
            $this->createStub(MediaProviderInterface::class),
            $media,
            'format'
        );

        static::assertSame('/thumb_25_format.foo', $publicUrl);
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

    /**
     * @doesNotPerformAssertions
     */
    public function testDeleteThumbnails(): void
    {
        $this->thumbnail->delete(
            $this->createStub(MediaProviderInterface::class),
            new Media(),
            'format'
        );
    }
}
