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

namespace Sonata\MediaBundle\Tests\Admin\ORM;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin;
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\MediaBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Provider\Pool;

class MediaAdminTest extends TestCase
{
    private $pool;
    private $categoryManager;
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = $this->prophesize(Pool::class);
        $this->categoryManager = $this->prophesize(CategoryManagerInterface::class);

        $this->mediaAdmin = new MediaAdmin(
            null,
            BaseMedia::class,
            'SonataMediaBundle:MediaAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
    }

    public function testItIsInstantiable(): void
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
