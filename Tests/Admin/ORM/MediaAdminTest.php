<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Admin\ORM;

use Sonata\MediaBundle\Admin\ORM\MediaAdmin;

class MediaAdminTest extends \PHPUnit_Framework_TestCase
{
    private $pool;
    private $categoryManager;
    private $mediaAdmin;

    protected function setUp()
    {
        $this->pool = $this->prophesize('Sonata\MediaBundle\Provider\Pool');
        $this->categoryManager = $this->prophesize('Sonata\MediaBundle\Model\CategoryManagerInterface');

        $this->mediaAdmin = new MediaAdmin(
            null,
            'Sonata\MediaBundle\Entity\BaseMedia',
            'SonataMediaBundle:MediaAdmin',
            $this->pool->reveal(),
            $this->categoryManager->reveal()
        );
    }

    public function testItIsInstantiable()
    {
        $this->assertNotNull($this->mediaAdmin);
    }
}
