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

namespace Sonata\MediaBundle\Tests\Admin;

use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Entity\CategoryManager;
use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Entity\BaseGallery;
use Sonata\MediaBundle\Provider\Pool;

class GalleryAdminTest extends TestCase
{
    protected $pool;
    protected $categoryManager;
    protected $mediaAdmin;

    protected function setUp(): void
    {
        $this->pool = $this->prophesize(Pool::class);
        $this->categoryManager = $this->prophesize(CategoryManager::class);

        $this->mediaAdmin = new GalleryAdmin(
            null,
            BaseGallery::class,
            'SonataMediaBundle:GalleryAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
    }

    public function testItIsInstantiable(): void
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
