<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Admin;

use Sonata\MediaBundle\Admin\GalleryItemAdmin;

class GalleryItemAdminTest extends \PHPUnit_Framework_TestCase
{
    private $mediaAdmin;

    protected function setUp()
    {
        $this->mediaAdmin = new GalleryItemAdmin(
            null,
            'Sonata\MediaBundle\Entity\BaseGallery',
            'SonataMediaBundle:GalleryAdmin'
        );
    }

    public function testItIsInstantiable()
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
