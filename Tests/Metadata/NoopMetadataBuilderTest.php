<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Metadata;

use Sonata\MediaBundle\Metadata\NoopMetadataBuilder;
use Sonata\MediaBundle\Model\MediaInterface;

class NoopMetadataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testNoop()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $filename = '/test/folder/testfile.png';

        $noopmetadatabuilder = new NoopMetadataBuilder();

        $this->assertEquals(array(), $noopmetadatabuilder->get($media, $filename));
    }

}
