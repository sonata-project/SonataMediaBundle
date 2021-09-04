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
use Sonata\MediaBundle\Entity\BaseGallery;
use Sonata\MediaBundle\Provider\Pool;

class GalleryAdminTest extends TestCase
{
    protected $mediaAdmin;

    protected function setUp(): void
    {
        $this->mediaAdmin = new GalleryAdmin(
            'media',
            BaseGallery::class,
            'SonataMediaBundle:GalleryAdmin',
            $this->createStub(Pool::class)
        );
    }

    public function testItIsInstantiable(): void
    {
        static::assertNotNull($this->mediaAdmin);
    }
}
