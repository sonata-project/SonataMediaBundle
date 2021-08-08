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
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin;
use Sonata\MediaBundle\Entity\BaseMedia;
use Sonata\MediaBundle\Provider\Pool;

class MediaAdminTest extends TestCase
{
    /**
     * @var MediaAdmin
     */
    private $mediaAdmin;

    protected function setUp(): void
    {
        $this->mediaAdmin = new MediaAdmin(
            'media',
            BaseMedia::class,
            'SonataMediaBundle:MediaAdmin',
            new Pool('default'),
            $this->createStub(CategoryManagerInterface::class)
        );
    }

    public function testItIsInstantiable(): void
    {
        self::assertNotNull($this->mediaAdmin);
    }
}
