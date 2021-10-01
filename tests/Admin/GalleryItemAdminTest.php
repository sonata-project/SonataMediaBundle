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
use Sonata\MediaBundle\Entity\BaseGalleryItem;

class GalleryItemAdminTest extends TestCase
{
    /**
     * @var GalleryItemAdmin
     */
    private $galleryItemAdmin;

    protected function setUp(): void
    {
        $this->galleryItemAdmin = new GalleryItemAdmin(
            'gallery',
            BaseGalleryItem::class,
            'SonataMediaBundle:GalleryAdmin'
        );
    }

    public function testItDoesNotHaveSubject(): void
    {
        static::assertFalse($this->galleryItemAdmin->hasSubject());
    }
}
