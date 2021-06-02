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
use Sonata\BlockBundle\Test\BlockServiceTestCase;
use Twig\Environment;

/**
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class BreadcrumbTest extends BlockServiceTestCase
{
    public function testBlockService(): void
    {
        $blockService = new BreadcrumbGalleryBlockService_Test(
            $this->createStub(Environment::class),
            $this->createStub(FactoryInterface::class)
        );

        $this->assertTrue($blockService->handleContext('context'));
    }
}
