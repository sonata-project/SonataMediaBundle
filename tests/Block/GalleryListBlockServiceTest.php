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

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Block\GalleryListBlockService;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class GalleryListBlockServiceTest extends BlockServiceTestCase
{
    protected $galleryManager;

    protected $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $this->galleryManager = $this->createMock(GalleryManagerInterface::class);
        $this->pool = $this->createMock(Pool::class);
    }

    public function testExecute(): void
    {
        $pager = $this->createMock(PagerInterface::class);
        $this->galleryManager->expects($this->once())->method('getPager')->willReturn($pager);

        $block = new Block();

        $settings = [
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'template' => '@SonataMedia/Block/block_gallery_list.html.twig',
        ];

        $blockContext = new BlockContext($block, $settings);

        $blockService = new GalleryListBlockService($this->twig, null, $this->galleryManager, $this->pool);

        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('@SonataMedia/Block/block_gallery_list.html.twig', [
                'context' => $blockContext,
                'pager' => $pager,
                'block' => $block,
                'settings' => $settings,
            ]);

        $blockService->execute($blockContext);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new GalleryListBlockService($this->twig, null, $this->galleryManager, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings([
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'title' => null,
            'translation_domain' => null,
            'icon' => 'fa fa-images',
            'class' => null,
            'template' => '@SonataMedia/Block/block_gallery_list.html.twig',
        ], $blockContext);
    }
}
