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
use Sonata\MediaBundle\Admin\GalleryItemAdmin;

class GalleryItemAdminTest extends TestCase
{
    private GalleryItemAdmin $galleryItemAdmin;

    protected function setUp(): void
    {
        $this->galleryItemAdmin = new GalleryItemAdmin();
    }

    public function testItDoesNotHaveSubject(): void
    {
        static::assertFalse($this->galleryItemAdmin->hasSubject());
    }
}
