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
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Sonata\MediaBundle\Block\GalleryListBlockService;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class GalleryListBlockServiceTest extends AbstractBlockServiceTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GalleryManagerInterface
     */
    protected $galleryManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Pool
     */
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
        $this->galleryManager->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $block = new Block();

        $blockContext = new BlockContext($block, [
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'template' => '@SonataMedia/Block/block_gallery_list.html.twig',
        ]);

        $blockService = new GalleryListBlockService('block.service', $this->templating, $this->galleryManager, $this->pool);
        $blockService->execute($blockContext);

        $this->assertSame('@SonataMedia/Block/block_gallery_list.html.twig', $this->templating->view);

        $this->assertSame($blockContext, $this->templating->parameters['context']);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertInstanceOf(BlockInterface::class, $this->templating->parameters['block']);
        $this->assertSame($pager, $this->templating->parameters['pager']);
    }

    public function testDefaultSettings(): void
    {
        $blockService = new GalleryListBlockService('block.service', $this->templating, $this->galleryManager, $this->pool);
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
