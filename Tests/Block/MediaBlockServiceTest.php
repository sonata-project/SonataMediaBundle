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
use Sonata\MediaBundle\Block\MediaBlockService;

class MediaBlockServiceTest extends AbstractBlockServiceTestCase
{
    protected $container;
    private $galleryManager;
    private $blockService;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->galleryManager = $this->prophesize('Sonata\MediaBundle\Model\GalleryManagerInterface');

        $this->blockService = new MediaBlockService(
            'block.service',
            $this->templating,
            $this->container->reveal(),
            $this->galleryManager->reveal()
        );
    }

    public function testExecute()
    {
        $block = $this->prophesize('Sonata\BlockBundle\Model\Block');
        $media = $this->prophesize('Sonata\MediaBundle\Model\MediaInterface');
        $blockContext = $this->prophesize('Sonata\BlockBundle\Block\BlockContext');

        $this->configureGetFormatChoices($media, array('format1' => 'format1'));
        $blockContext->getBlock()->willReturn($block->reveal());
        $blockContext->getSetting('format')->willReturn('format');
        $blockContext->setSetting('format', 'format1')->shouldBeCalled();
        $blockContext->getSettings()->willReturn(array());
        $blockContext->getTemplate()->willReturn('template');
        $blockContext->getSetting('mediaId')->willReturn($media->reveal());
        $block->getSetting('mediaId')->willReturn($media->reveal());

        $this->blockService->execute($blockContext->reveal());

        $this->assertSame('template', $this->templating->view);
        $this->assertInternalType('array', $this->templating->parameters['settings']);
        $this->assertSame($media->reveal(), $this->templating->parameters['media']);
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
            'media' => false,
            'mediaId' => null,
            'template' => 'SonataMediaBundle:Block:block_media.html.twig',
            'title' => false,
            'ttl' => 0,
            'use_cache' => true,
        ), $blockContext);
    }

    private function configureGetFormatChoices($media, $choices)
    {
        $mediaAdmin = $this->prophesize('Sonata\MediaBundle\Admin\BaseMediaAdmin');
        $pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');

        $this->container->get('sonata.media.admin.media')->willReturn($mediaAdmin->reveal());
        $mediaAdmin->getPool()->willReturn($pool->reveal());
        $media->getContext()->willReturn('context');
        $pool->getFormatNamesByContext('context')->willReturn($choices);
    }
}
