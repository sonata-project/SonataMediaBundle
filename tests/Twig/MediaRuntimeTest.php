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

namespace Sonata\MediaBundle\Tests\Twig;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Twig\Environment;

class MediaRuntimeTest extends TestCase
{
    private Pool $pool;

    /**
     * @var MockObject&MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var MockObject&Environment
     */
    private $twig;

    /**
     * @var MockObject&MediaProviderInterface
     */
    private $provider;

    private MediaRuntime $mediaRuntime;

    protected function setUp(): void
    {
        $this->mediaManager = $this->createMock(MediaManagerInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->provider = $this->createMock(MediaProviderInterface::class);

        $this->pool = new Pool('default_context');
        $this->pool->addProvider('provider', $this->provider);

        $this->mediaRuntime = new MediaRuntime($this->pool, $this->mediaManager, $this->twig);
    }

    /**
     * @psalm-suppress NullArgument
     *
     * This is to ensure we throw an exception when null value is provided.
     * It is enforced with a test because we can't add typehint with unions yet.
     */
    public function testWithNullInput(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Media parameter must be either an identifier or the media itself for Twig functions, "NULL" given.');

        // @phpstan-ignore-next-line
        static::assertSame('', $this->mediaRuntime->media(null, 'big'));
        // @phpstan-ignore-next-line
        static::assertSame('', $this->mediaRuntime->thumbnail(null, 'big'));
        // @phpstan-ignore-next-line
        static::assertSame('', $this->mediaRuntime->path(null, 'big'));
    }

    public function testWithNonMediaInput(): void
    {
        $media = new Media();

        static::assertSame('', $this->mediaRuntime->media('1', 'big'));
        static::assertSame('', $this->mediaRuntime->media(1, 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('', $this->mediaRuntime->path('1', 'big'));
        static::assertSame('', $this->mediaRuntime->path(1, 'big'));

        $this->mediaManager->method('find')->willReturn($media);

        static::assertSame('', $this->mediaRuntime->media('1', 'big'));
        static::assertSame('', $this->mediaRuntime->media(1, 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('', $this->mediaRuntime->path('1', 'big'));
        static::assertSame('', $this->mediaRuntime->path(1, 'big'));
    }

    public function testCantRenderWithoutTemplate(): void
    {
        $media = new Media();

        $this->mediaManager->method('find')->willReturn($media);
        $media->setProviderName('provider');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        static::assertSame('', $this->mediaRuntime->media('1', 'big'));
        static::assertSame('', $this->mediaRuntime->media(1, 'big'));
        static::assertSame('', $this->mediaRuntime->media($media, 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail($media, 'big'));
    }

    public function testMediaRenders(): void
    {
        $media = new Media();
        $media->setProviderName('provider');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->mediaManager->method('find')->willReturn($media);
        $this->provider->method('getFormatName')->willReturn('big');
        $this->provider->method('getHelperProperties')->willReturn([]);
        $this->provider->method('getTemplate')->willReturn('template');
        $this->twig->expects(static::exactly(3))->method('render')->with('template', [
            'media' => $media,
            'format' => 'big',
            'options' => [],
        ])->willReturn('rendered');

        static::assertSame('rendered', $this->mediaRuntime->media('1', 'big'));
        static::assertSame('rendered', $this->mediaRuntime->media(1, 'big'));
        static::assertSame('rendered', $this->mediaRuntime->media($media, 'big'));
    }

    public function testThumbnailWithoutFormatSizes(): void
    {
        $media = new Media();
        $media->setProviderName('provider');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->mediaManager->method('find')->willReturn($media);
        $this->provider->method('getFormatName')->willReturn('big');
        $this->provider->method('getHelperProperties')->willReturn([]);
        $this->provider->method('getTemplate')->willReturn('template');
        $this->twig->expects(static::exactly(6))->method('render')->with('template', [
            'media' => $media,
            'options' => [
                'title' => null,
                'alt' => null,
                'src' => null,
            ],
        ])->willReturn('rendered');

        $this->provider->method('getFormat')->willReturn(false);

        static::assertSame('rendered', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail($media, 'big'));

        $this->provider->method('getFormat')->willReturn([]);

        static::assertSame('rendered', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail($media, 'big'));
    }

    public function testThumbnailRenders(): void
    {
        $media = new Media();
        $media->setName('Name');
        $media->setProviderName('provider');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->mediaManager->method('find')->willReturn($media);
        $this->provider->method('getFormat')->willReturn([
            'width' => 25,
            'height' => 30,
        ]);
        $this->provider->method('getFormatName')->willReturn('big');
        $this->provider->method('getHelperProperties')->willReturn([]);
        $this->provider->method('getTemplate')->willReturn('template');
        $this->provider->method('generatePublicUrl')->with(
            $media,
            'big'
        )->willReturn('public_url');
        $this->twig->expects(static::exactly(3))->method('render')->with('template', [
            'media' => $media,
            'options' => [
                'title' => 'Name',
                'alt' => 'Name',
                'width' => 25,
                'height' => 30,
                'src' => 'public_url',
            ],
        ])->willReturn('rendered');

        static::assertSame('rendered', $this->mediaRuntime->thumbnail('1', 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail(1, 'big'));
        static::assertSame('rendered', $this->mediaRuntime->thumbnail($media, 'big'));
    }

    public function testPathRendersPublicUrl(): void
    {
        $media = new Media();
        $media->setProviderName('provider');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->mediaManager->method('find')->willReturn($media);
        $this->provider->method('getFormatName')->willReturn('big');
        $this->provider->expects(static::exactly(3))->method('generatePublicUrl')->with(
            $media,
            'big'
        )->willReturn('public_url');

        static::assertSame('public_url', $this->mediaRuntime->path('1', 'big'));
        static::assertSame('public_url', $this->mediaRuntime->path(1, 'big'));
        static::assertSame('public_url', $this->mediaRuntime->path($media, 'big'));
    }
}
