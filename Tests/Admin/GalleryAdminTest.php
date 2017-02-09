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

use Sonata\MediaBundle\Admin\GalleryAdmin;

class GalleryAdminTest extends \PHPUnit_Framework_TestCase
{
    protected $pool;
    protected $categoryManager;
    protected $mediaAdmin;

    protected function setUp()
    {
        $this->pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $this->categoryManager = $this->prophesize('Sonata\ClassificationBundle\Entity\CategoryManager');

        $this->mediaAdmin = new GalleryAdmin(
            null,
            'Sonata\MediaBundle\Entity\BaseGallery',
            'SonataMediaBundle:GalleryAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
    }

    public function testItIsInstantiable()
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
