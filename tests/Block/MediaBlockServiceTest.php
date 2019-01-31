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

use Prophecy\Prophecy\ObjectProphecy;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\MediaBundle\Admin\BaseMediaAdmin;
use Sonata\MediaBundle\Block\MediaBlockService;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MediaBlockServiceTest extends AbstractBlockServiceTestCase
{
    protected $container;
    private $galleryManager;
    private $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->galleryManager = $this->prophesize(GalleryManagerInterface::class);

        $this->blockService = new MediaBlockService(
            'block.service',
            $this->templating,
            $this->container->reveal(),
            $this->galleryManager->reveal()
        );
    }

    public function testExecute(): void
    {
        $block = $this->prophesize(Block::class);
        $media = $this->prophesize(MediaInterface::class);
        $blockContext = $this->prophesize(BlockContext::class);

        $this->configureGetFormatChoices($media, ['format1' => 'format1']);
        $blockContext->getBlock()->willReturn($block->reveal());
        $blockContext->getSetting('format')->willReturn('format');
        $blockContext->setSetting('format', 'format1')->shouldBeCalled();
        $blockContext->getSettings()->willReturn([]);
        $blockContext->getTemplate()->willReturn('template');
        $blockContext->getSetting('mediaId')->willReturn($media->reveal());
        $block->getSetting('mediaId')->willReturn($media->reveal());

        $this->blockService->execute($blockContext->reveal());

        $this->assertSame('template', $this->templating->view);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertSame($media->reveal(), $this->templating->parameters['media']);
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

    private function configureGetFormatChoices(ObjectProphecy $media, array $choices): void
    {
        $mediaAdmin = $this->prophesize(BaseMediaAdmin::class);
        $pool = $this->prophesize(Pool::class);

        $this->container->get('sonata.media.admin.media')->willReturn($mediaAdmin->reveal());
        $mediaAdmin->getPool()->willReturn($pool->reveal());
        $media->getContext()->willReturn('context');
        $pool->getFormatNamesByContext('context')->willReturn($choices);
    }
}
