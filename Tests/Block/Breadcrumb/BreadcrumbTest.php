<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Block\Breadcrumb;

use Sonata\BlockBundle\Test\AbstractBlockServiceTestCase;
use Sonata\MediaBundle\Block\Breadcrumb\BaseGalleryBreadcrumbBlockService;

class BreadcrumbGalleryBlockService_Test extends BaseGalleryBreadcrumbBlockService
{
}

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class BreadcrumbTest extends AbstractBlockServiceTestCase
{
    public function testBlockService()
    {
        $blockService = new BreadcrumbGalleryBlockService_Test(
            'context',
            'name',
            $this->prophesize('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')->reveal(),
            $this->prophesize('Knp\Menu\Provider\MenuProviderInterface')->reveal(),
            $this->prophesize('Knp\Menu\FactoryInterface')->reveal()
        );

        $this->assertTrue($blockService->handleContext('context'));
    }
}
