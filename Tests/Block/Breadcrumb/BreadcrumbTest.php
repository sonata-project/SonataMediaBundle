<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Block\Breadcrumb;

use Sonata\MediaBundle\Block\Breadcrumb\BaseGalleryBreadcrumbBlockService;
use Sonata\SeoBundle\Tests\Block\BaseBlockTest;

class BreadcrumbGalleryBlockService_Test extends BaseGalleryBreadcrumbBlockService
{

}

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class BreadcrumbTest extends BaseBlockTest
{
    public function testBlockService()
    {
        $blockService = new BreadcrumbGalleryBlockService_Test(
            'context',
            'name',
            $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface'),
            $this->getMock('Knp\Menu\Provider\MenuProviderInterface'),
            $this->getMock('Knp\Menu\FactoryInterface')
        );

        $this->assertTrue($blockService->handleContext('context'));
    }
}
