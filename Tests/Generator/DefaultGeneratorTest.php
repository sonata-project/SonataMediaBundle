<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Generator;

use Sonata\MediaBundle\Generator\DefaultGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

class DefaultGeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testProvider()
    {

        $generator = new DefaultGenerator;

        $media = new Media;
        $media->setContext('user');

        $media->setId(10);
        $this->assertEquals('user/0001/01', $generator->generatePath($media));

        $media->setId(10000);
        $this->assertEquals('user/0001/11', $generator->generatePath($media));

        $media->setId(12341230);
        $this->assertEquals('user/0124/42', $generator->generatePath($media));

        $media->setId(999999999);
        $this->assertEquals('user/10000/100', $generator->generatePath($media));

    }
}
