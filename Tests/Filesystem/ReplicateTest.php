<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Filesystem;

use Sonata\MediaBundle\Filesystem\Replicate;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    public function testReplicate()
    {
        $master = $this->getMock('Gaufrette\Adapter');
        $slave = $this->getMock('Gaufrette\Adapter');
        $replicate = new Replicate($master, $slave);

        $master->expects($this->once())->method('mtime')->will($this->returnValue('master'));
        $slave->expects($this->never())->method('mtime');
        $this->assertEquals('master', $replicate->mtime('foo'));

        $master->expects($this->once())->method('delete')->will($this->returnValue('master'));
        $slave->expects($this->once())->method('delete')->will($this->returnValue('master'));
        $replicate->delete('foo');

        $master->expects($this->once())->method('keys')->will($this->returnValue(array()));
        $slave->expects($this->never())->method('keys')->will($this->returnValue(array()));
        $this->assertInternalType('array', $replicate->keys());

        $master->expects($this->once())->method('exists')->will($this->returnValue(true));
        $slave->expects($this->never())->method('exists');
        $this->assertTrue($replicate->exists('foo'));

        $master->expects($this->once())->method('write')->will($this->returnValue(123));
        $slave->expects($this->once())->method('write')->will($this->returnValue(123));
        $this->assertEquals(123, $replicate->write('foo', 'contents'));

        $master->expects($this->once())->method('read')->will($this->returnValue('master content'));
        $slave->expects($this->never())->method('read');
        $this->assertEquals('master content', $replicate->read('foo'));

        $master->expects($this->once())->method('rename');
        $slave->expects($this->once())->method('rename');
        $replicate->rename('foo', 'bar');

        $master->expects($this->once())->method('isDirectory');
        $slave->expects($this->never())->method('isDirectory');
        $replicate->isDirectory('foo');
    }
}
