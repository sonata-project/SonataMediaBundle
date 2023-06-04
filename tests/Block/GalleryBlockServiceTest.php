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
use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\MediaBundle\Block\GalleryBlockService;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Model\GalleryItemInterface;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class GalleryBlockServiceTest extends BlockServiceTestCase
{
    private GalleryBlockService $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AdminInterface<GalleryInterface<GalleryItemInterface>>&Stub $galleryAdmin */
        $galleryAdmin = $this->createStub(AdminInterface::class);

        $this->blockService = new GalleryBlockService(
            $this->twig,
            new Pool('default'),
            $galleryAdmin,
            $this->createStub(GalleryManagerInterface::class)
        );
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
            ->expects(static::once())
            ->method('render')
            ->with('template', [
                'gallery' => $gallery,
                'block' => $block,
                'elements' => [],
                'settings' => ['settings'],
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
            'gallery' => false,
            'galleryId' => null,
            'pauseTime' => 3000,
            'startPaused' => false,
            'template' => '@SonataMedia/Block/block_gallery.html.twig',
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
}
