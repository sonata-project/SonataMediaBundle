<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Block\Service;

use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Model\Block;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
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

    protected function setUp()
    {
        parent::setUp();

        $this->galleryManager = $this->getMock('Sonata\MediaBundle\Model\GalleryManagerInterface');
        $this->pool = $this->getMockBuilder('Sonata\MediaBundle\Provider\Pool')->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $pager = $this->getMock('Sonata\DatagridBundle\Pager\PagerInterface');
        $this->galleryManager->expects($this->once())->method('getPager')->will($this->returnValue($pager));

        $block = new Block();

        $blockContext = new BlockContext($block, array(
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'title' => 'Gallery List',
            'template' => 'SonataMediaBundle:Block:block_gallery_list.html.twig',
        ));

        $blockService = new GalleryListBlockService('block.service', $this->templating, $this->galleryManager, $this->pool);
        $blockService->execute($blockContext);

        $this->assertSame('SonataMediaBundle:Block:block_gallery_list.html.twig', $this->templating->view);

        $this->assertSame($blockContext, $this->templating->parameters['context']);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertInstanceOf('Sonata\BlockBundle\Model\BlockInterface', $this->templating->parameters['block']);
        $this->assertSame($pager, $this->templating->parameters['pager']);
    }

    public function testDefaultSettings()
    {
        $blockService = new GalleryListBlockService('block.service', $this->templating, $this->galleryManager, $this->pool);
        $blockContext = $this->getBlockContext($blockService);

        $this->assertSettings(array(
            'number' => 15,
            'mode' => 'public',
            'order' => 'createdAt',
            'sort' => 'desc',
            'context' => false,
            'title' => 'Gallery List',
            'template' => 'SonataMediaBundle:Block:block_gallery_list.html.twig',
        ), $blockContext);
    }
}
