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
use Sonata\MediaBundle\Admin\GalleryAdmin;
use Sonata\MediaBundle\Provider\Pool;

class GalleryAdminTest extends TestCase
{
    private GalleryAdmin $galleryAdmin;

    protected function setUp(): void
    {
        $this->galleryAdmin = new GalleryAdmin(
            new Pool('default')
        );
    }

    public function testItDoesNotHaveSubject(): void
    {
        static::assertFalse($this->galleryAdmin->hasSubject());
    }
}
