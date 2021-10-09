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
    /**
     * @var Pool
     */
    private $pool;

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

    /**
     * @var MediaRuntime
     */
    private $mediaRuntime;

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
     * @group legacy
     */
    public function testWithNullInput(): void
    {
        static::assertSame('', $this->mediaRuntime->media(null, 'big'));
        static::assertSame('', $this->mediaRuntime->thumbnail(null, 'big'));
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
