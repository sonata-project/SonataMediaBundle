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

namespace Sonata\MediaBundle\Tests\Block;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\MediaBundle\Block\MediaBlockService;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaBlockServiceTest extends BlockServiceTestCase
{
    private Pool $pool;

    private MediaBlockService $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pool = new Pool('default');

        /** @var AdminInterface<MediaInterface>&Stub $mediaAdmin */
        $mediaAdmin = $this->createStub(AdminInterface::class);

        $this->blockService = new MediaBlockService(
            $this->twig,
            $this->pool,
            $mediaAdmin,
            $this->createStub(MediaManagerInterface::class)
        );
    }

    public function testExecute(): void
    {
        $block = $this->createMock(Block::class);
        $media = $this->createMock(MediaInterface::class);
        $blockContext = $this->createMock(BlockContextInterface::class);

        $this->configureFormat($media, 'format1');
        $blockContext->method('getBlock')->willReturn($block);
        $blockContext->method('getSetting')->willReturnMap([
            ['format', 'format'],
            ['mediaId', $media],
        ]);
        $blockContext->expects(static::once())->method('setSetting')->with('format', 'format1');
        $blockContext->method('getSettings')->willReturn([]);
        $blockContext->method('getTemplate')->willReturn('template');
        $block->method('getSetting')->with('mediaId')->willReturn($media);

        $this->twig
            ->expects(static::once())
            ->method('render')
            ->with('template', [
                'media' => $media,
                'block' => $block,
                'settings' => [],
            ]);

        $this->blockService->execute($blockContext);
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function testDefaultSettings(): void
    {
        $blockContext = $this->getBlockContext($this->blockService);

        $settings = [
            'attr' => [],
            'context' => false,
            'format' => false,
            'media' => false,
            'mediaId' => null,
            'template' => '@SonataMedia/Block/block_media.html.twig',
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
        ];

        // TODO: Remove if when dropping support for sonata-project/block-bundle < 5.0
        if (class_exists(HttpCacheHandler::class)) {
            $settings['extra_cache_keys'] = [];
            $settings['ttl'] = 0;
            $settings['use_cache'] = true;
        }

        $this->assertSettings($settings, $blockContext);
    }

    private function configureFormat(MockObject $media, string $format): void
    {
        $media->method('getContext')->with()->willReturn('context');

        $this->pool->addContext('context', [], [$format => [
            'width' => null,
            'height' => null,
            'quality' => 80,
            'format' => 'jpg',
            'constraint' => false,
            'resizer' => null,
            'resizer_options' => [],
        ]]);
    }
}
