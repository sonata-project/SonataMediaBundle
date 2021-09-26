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

namespace Sonata\MediaBundle\Tests\Messenger;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Messenger\GenerateThumbnailsHandler;
use Sonata\MediaBundle\Messenger\GenerateThumbnailsMessage;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

/**
 * @author Jordi Sala Morales <jordism91@gmail.name>
 */
final class GenerateThumbnailsHandlerTest extends TestCase
{
    /**
     * @var MockObject&ThumbnailInterface
     */
    private $thumbnail;

    /**
     * @var MockObject&MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var MockObject&Pool
     */
    private $pool;

    /**
     * @var GenerateThumbnailsHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->thumbnail = $this->createMock(ThumbnailInterface::class);
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->pool = $this->createMock(Pool::class);

        $this->handler = new GenerateThumbnailsHandler($this->thumbnail, $this->mediaManager, $this->pool);
    }

    public function testMediaNotFound(): void
    {
        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Media "25" not found.');

        $this->handler->__invoke(new GenerateThumbnailsMessage(25));
    }

    public function testMediaWithoutMediaProvider(): void
    {
        $this->mediaManager->method('find')->with(25)->willReturn(new Media());

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Media "25" does not have a provider name.');

        $this->handler->__invoke(new GenerateThumbnailsMessage(25));
    }

    public function testMediaProviderNotFoundOnPool(): void
    {
        $media = new Media();
        $media->setProviderName('provider_name');

        $this->mediaManager->method('find')->with(25)->willReturn($media);
        $this->pool->method('getProvider')->with('provider_name')->willThrowException(new \InvalidArgumentException());

        $this->expectException(UnrecoverableMessageHandlingException::class);
        $this->expectExceptionMessage('Provider "provider_name" not found.');

        $this->handler->__invoke(new GenerateThumbnailsMessage(25));
    }

    /**
     * @dataProvider provideMediaIds
     *
     * @param int|string $id
     */
    public function testGenerateThumbnails($id): void
    {
        $media = new Media();
        $media->setProviderName('provider_name');

        $provider = $this->createStub(MediaProviderInterface::class);

        $this->mediaManager->method('find')->with($id)->willReturn($media);
        $this->pool->method('getProvider')->with('provider_name')
            ->willReturn($provider);

        $this->thumbnail->expects(static::once())->method('generate')->with($provider, $media);

        $this->handler->__invoke(new GenerateThumbnailsMessage($id));
    }

    /**
     * @phpstan-return iterable<array{int|string}>
     */
    public function provideMediaIds(): iterable
    {
        yield [25];
        yield ['25'];
    }
}
