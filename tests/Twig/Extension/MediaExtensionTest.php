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

namespace Sonata\MediaBundle\Tests\Twig\Extension;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Twig\Environment;
use Twig\Node\Node;
use Twig\TwigFunction;

/**
 * @author Geza Buza <bghome@gmail.com>
 */
class MediaExtensionTest extends TestCase
{
    /**
     * @var MockObject&MediaProviderInterface
     */
    private $provider;

    /**
     * @var MockObject&Environment
     */
    private $twig;

    /**
     * @var MediaExtension
     */
    private $mediaExtension;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->provider = $this->createMock(MediaProviderInterface::class);

        $pool = $this->createStub(Pool::class);
        $pool->method('getProvider')->willReturn($this->provider);

        $this->mediaExtension = new MediaExtension(
            $pool,
            $this->createStub(MediaManagerInterface::class)
        );
        $this->mediaExtension->initRuntime($this->twig);
    }

    public function testDefinesFunctions(): void
    {
        $functions = $this->mediaExtension->getFunctions();

        static::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);
        static::assertCount(3, $functions);

        static::assertSame('sonata_media', $functions[0]->getName());
        static::assertSame('sonata_thumbnail', $functions[1]->getName());
        static::assertSame('sonata_path', $functions[2]->getName());

        static::assertSame([MediaRuntime::class, 'media'], $functions[0]->getCallable());
        static::assertSame([MediaRuntime::class, 'thumbnail'], $functions[1]->getCallable());
        static::assertSame([MediaRuntime::class, 'path'], $functions[2]->getCallable());

        static::assertSame(['html'], $functions[0]->getSafe(new Node()));
        static::assertSame(['html'], $functions[1]->getSafe(new Node()));
    }

    /**
     * NEXT_MAJOR: Remove this test.
     *
     * @group legacy
     */
    public function testThumbnailHasAllNecessaryAttributes(): void
    {
        $media = new Media();
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->provider->method('getTemplate')->willReturn('template');
        $this->provider->method('getFormatName')->willReturn('big');
        $this->provider->method('getFormat')->willReturn(false);
        $this->provider->expects(static::once())->method('generatePublicUrl')->with($media, 'big')
            ->willReturn('http://some.url.com');

        $this->twig->expects(static::once())->method('render')->with('template', [
            'media' => $media,
            'options' => [
                'title' => 'Test title',
                'alt' => 'Test title',
                'src' => 'http://some.url.com',
            ],
        ])->willReturn('render');

        $this->mediaExtension->thumbnail($media, 'big', [
            'title' => 'Test title',
            'alt' => 'Test title',
        ]);
    }
}
