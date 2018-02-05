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
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\MediaBundle\Block\GalleryBlockService;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GalleryBlockServiceTest extends AbstractBlockServiceTestCase
{
    protected $container;
    private $galleryManager;
    private $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->galleryManager = $this->prophesize(GalleryManagerInterface::class);

        $this->blockService = new GalleryBlockService(
            'block.service',
            $this->templating,
            $this->container->reveal(),
            $this->galleryManager->reveal()
        );
    }

    public function testExecute(): void
    {
        $block = $this->prophesize(Block::class);
        $gallery = $this->prophesize(GalleryInterface::class);
        $blockContext = $this->prophesize(BlockContext::class);

        $blockContext->getBlock()->willReturn($block->reveal());
        $blockContext->getSettings()->willReturn(['settings']);
        $blockContext->getTemplate()->willReturn('template');
        $block->getSetting('galleryId')->willReturn($gallery->reveal());
        $gallery->getGalleryItems()->willReturn([]);

        $this->blockService->execute($blockContext->reveal());

        $this->assertSame('template', $this->templating->view);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertInternalType('array', $this->templating->parameters['elements']);
        $this->assertSame($gallery->reveal(), $this->templating->parameters['gallery']);
        $this->assertSame($block->reveal(), $this->templating->parameters['block']);
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
