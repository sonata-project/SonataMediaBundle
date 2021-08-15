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

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Block\GalleryBlockService;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\Pool;

class GalleryBlockServiceTest extends BlockServiceTestCase
{
    /**
     * @var GalleryBlockService
     */
    private $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blockService = new GalleryBlockService(
            $this->twig,
            new Pool('default'),
            $this->createStub(AdminInterface::class),
            $this->createStub(ManagerInterface::class)
        );
    }

    public function testName(): void
    {
        self::assertSame('Media Gallery', $this->blockService->getName());
    }

    public function testExecute(): void
    {
        $block = $this->createMock(Block::class);
        $gallery = $this->createStub(GalleryInterface::class);
        $blockContext = $this->createStub(BlockContextInterface::class);

        $blockContext->method('getBlock')->willReturn($block);
        $blockContext->method('getSettings')->willReturn(['settings']);
        $blockContext->method('getTemplate')->willReturn('template');
        $block->method('getSetting')->with('galleryId')->willReturn($gallery);
        $gallery->method('getGalleryItems')->willReturn(new ArrayCollection());

        $this->twig
            ->expects(self::once())
            ->method('render')
            ->with('template', [
                'gallery' => $gallery,
                'block' => $block,
                'elements' => [],
                'settings' => ['settings'],
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
            'gallery' => false,
            'galleryId' => null,
            'pauseTime' => 3000,
            'startPaused' => false,
            'template' => '@SonataMedia/Block/block_gallery.html.twig',
            'title' => null,
            'translation_domain' => null,
            'icon' => null,
            'class' => null,
            'ttl' => 0,
            'use_cache' => true,
        ], $blockContext);
    }
}
