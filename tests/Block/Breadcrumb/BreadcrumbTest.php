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

namespace Sonata\MediaBundle\Tests\Block\Breadcrumb;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class BreadcrumbTest extends AbstractBlockServiceTestCase
{
    public function testBlockService(): void
    {
        $blockService = new BreadcrumbGalleryBlockService_Test(
            'context',
            'name',
            $this->prophesize(EngineInterface::class)->reveal(),
            $this->prophesize(MenuProviderInterface::class)->reveal(),
            $this->prophesize(FactoryInterface::class)->reveal()
        );

        $this->assertTrue($blockService->handleContext('context'));
    }
}
