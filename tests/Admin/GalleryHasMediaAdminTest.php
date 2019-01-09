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
use Sonata\MediaBundle\Admin\GalleryHasMediaAdmin;
use Sonata\MediaBundle\Entity\BaseGallery;

class GalleryHasMediaAdminTest extends TestCase
{
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->mediaAdmin = new GalleryHasMediaAdmin(
            null,
            BaseGallery::class,
            'SonataMediaBundle:GalleryAdmin'
        );
    }

    public function testItIsInstantiable(): void
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
