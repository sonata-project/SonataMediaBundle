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
use Sonata\MediaBundle\Block\FeatureMediaBlockService;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FeatureMediaBlockServiceTest extends AbstractBlockServiceTestCase
{
    protected $container;
    private $galleryManager;
    private $blockService;

    protected function setUp()
    {
        parent::setUp();

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->galleryManager = $this->prophesize(GalleryManagerInterface::class);

        $this->blockService = new FeatureMediaBlockService(
            'block.service',
            $this->templating,
            $this->container->reveal(),
            $this->galleryManager->reveal()
        );
    }

    public function testDefaultSettings()
    {
        $blockContext = $this->getBlockContext($this->blockService);

        $this->assertSettings([
            'attr' => [],
            'content' => false,
            'context' => false,
            'extra_cache_keys' => [],
            'format' => false,
            'media' => false,
            'mediaId' => null,
            'orientation' => 'left',
            'template' => '@SonataMedia/Block/block_feature_media.html.twig',
            'title' => false,
            'ttl' => 0,
            'use_cache' => true,
        ], $blockContext);
    }
}
