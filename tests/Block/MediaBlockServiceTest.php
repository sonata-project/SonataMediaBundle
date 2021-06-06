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
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Block\MediaBlockService;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var MockObject&ContainerInterface
     */
    protected $container;

    /**
     * @var MediaBlockService
     */
    private $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->createMock(ContainerInterface::class);

        $this->blockService = new MediaBlockService(
            $this->twig,
            $this->container,
            $this->createStub(GalleryManagerInterface::class)
        );
    }

    public function testExecute(): void
    {
        $block = $this->createMock(Block::class);
        $media = $this->createMock(MediaInterface::class);
        $blockContext = $this->createMock(BlockContextInterface::class);

        $this->configureGetFormatChoices($media, ['format1' => 'format1']);
        $blockContext->method('getBlock')->willReturn($block);
        $blockContext->method('getSetting')->willReturnMap([
            ['format', 'format'],
            ['mediaId', $media],
        ]);
        $blockContext->expects($this->once())->method('setSetting')->with('format', 'format1');
        $blockContext->method('getSettings')->willReturn([]);
        $blockContext->method('getTemplate')->willReturn('template');
        $block->method('getSetting')->with('mediaId')->willReturn($media);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('template', [
                'media' => $media,
                'block' => $block,
                'settings' => [],
            ]);

        $this->blockService->execute($blockContext);
    }

    public function testDefaultSettings(): void
    {
        $blockContext = $this->getBlockContext($this->blockService);

        $this->assertSettings([
            'attr' => [],
            'context' => false,
            'extra_cache_keys' => [],
            'format' => false,
            'media' => false,
            'mediaId' => null,
            'template' => '@SonataMedia/Block/block_media.html.twig',
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'ttl' => 0,
            'use_cache' => true,
        ], $blockContext);
    }

    private function configureGetFormatChoices(MockObject $media, array $choices): void
    {
        $mediaAdmin = $this->createMock(BaseMediaAdmin::class);
        $pool = $this->createMock(Pool::class);

        $this->container->method('get')->with('sonata.media.admin.media')->willReturn($mediaAdmin);
        $mediaAdmin->method('getPool')->with()->willReturn($pool);
        $media->method('getContext')->with()->willReturn('context');
        $pool->method('getFormatNamesByContext')->with('context')->willReturn($choices);
    }
}
