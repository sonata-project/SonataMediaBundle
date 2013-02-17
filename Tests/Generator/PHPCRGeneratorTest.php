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

use Sonata\MediaBundle\Generator\PHPCRGenerator;
use Sonata\MediaBundle\Tests\Entity\Media;

class PHPCRGeneratorTest extends \PHPUnit_Framework_TestCase
{

    public function testPHPCRGenerator()
    {

        $generator = new PHPCRGenerator;

        $media = new Media;
        $media->setContext('user');

        $media->setId('nodename');
        $this->assertEquals('user', $generator->generatePath($media));

        $media->setId('/media/nodename');
        $this->assertEquals('user/media', $generator->generatePath($media));

        $media->setId('/media/sub/path/nodename');
        $this->assertEquals('user/media/sub/path', $generator->generatePath($media));
    }
}
