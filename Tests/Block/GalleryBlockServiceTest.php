<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Block;

use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\MediaBundle\Block\GalleryBlockService;

class GalleryBlockServiceTest extends AbstractBlockServiceTestCase
{
    protected $container;
    private $galleryManager;
    private $blockService;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->galleryManager = $this->prophesize('Sonata\MediaBundle\Model\GalleryManagerInterface');

        $this->blockService = new GalleryBlockService(
            'block.service',
            $this->templating,
            $this->container->reveal(),
            $this->galleryManager->reveal()
        );
    }

    public function testExecute()
    {
        $block = $this->prophesize('Sonata\BlockBundle\Model\Block');
        $gallery = $this->prophesize('Sonata\MediaBundle\Model\GalleryInterface');
        $blockContext = $this->prophesize('Sonata\BlockBundle\Block\BlockContext');

        $blockContext->getBlock()->willReturn($block->reveal());
        $blockContext->getSettings()->willReturn(array('settings'));
        $blockContext->getTemplate()->willReturn('template');
        $block->getSetting('galleryId')->willReturn($gallery->reveal());
        $gallery->getGalleryItems()->willReturn(array());

        $this->blockService->execute($blockContext->reveal());

        $this->assertSame('template', $this->templating->view);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertInternalType('array', $this->templating->parameters['elements']);
        $this->assertSame($gallery->reveal(), $this->templating->parameters['gallery']);
        $this->assertSame($block->reveal(), $this->templating->parameters['block']);
    }

    public function testDefaultSettings()
    {
        $blockContext = $this->getBlockContext($this->blockService);

        $this->assertSettings(array(
            'attr' => array(),
            'context' => false,
            'extra_cache_keys' => array(),
            'format' => false,
            'gallery' => false,
            'galleryId' => null,
            'pauseTime' => 3000,
            'startPaused' => false,
            'template' => 'SonataMediaBundle:Block:block_gallery.html.twig',
            'title' => false,
            'ttl' => 0,
            'use_cache' => true,
        ), $blockContext);
    }
}
