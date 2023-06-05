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

use PHPUnit\Framework\MockObject\Stub;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Cache\HttpCacheHandler;
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Sonata\MediaBundle\Block\FeatureMediaBlockService;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class FeatureMediaBlockServiceTest extends BlockServiceTestCase
{
    private FeatureMediaBlockService $blockService;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var AdminInterface<MediaInterface>&Stub $mediaAdmin */
        $mediaAdmin = $this->createStub(AdminInterface::class);

        $this->blockService = new FeatureMediaBlockService(
            $this->twig,
            new Pool('default'),
            $mediaAdmin,
            $this->createStub(MediaManagerInterface::class)
        );
    }

    /**
     * @psalm-suppress DeprecatedClass
     */
    public function testDefaultSettings(): void
    {
        $blockContext = $this->getBlockContext($this->blockService);

        $settings = [
            'attr' => [],
            'content' => false,
            'context' => false,
            'format' => false,
            'media' => false,
            'mediaId' => null,
            'orientation' => 'left',
            'template' => '@SonataMedia/Block/block_feature_media.html.twig',
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
