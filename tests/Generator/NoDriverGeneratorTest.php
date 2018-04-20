<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Exception\NoDriverException;
use Sonata\MediaBundle\Generator\NoDriverGenerator;
use Sonata\MediaBundle\Model\MediaInterface;

class NoDriverGeneratorTest extends TestCase
{
    public function testException()
    {
        $this->expectException(NoDriverException::class);

        $manager = new NoDriverGenerator();
        $manager->generatePath($this->createMock(MediaInterface::class));
    }
}
