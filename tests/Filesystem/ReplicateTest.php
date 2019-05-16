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

namespace Sonata\MediaBundle\Tests\Filesystem;

use Gaufrette\Adapter;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Filesystem\Replicate;

class ReplicateTest extends TestCase
{
    public function testReplicate(): void
    {
        $master = $this->createMock(Adapter::class);
        $slave = $this->createMock(Adapter::class);
        $replicate = new Replicate($master, $slave);

        $master->expects($this->once())->method('mtime')->willReturn('master');
        $slave->expects($this->never())->method('mtime');
        $this->assertSame('master', $replicate->mtime('foo'));

        $master->expects($this->once())->method('delete')->willReturn('master');
        $slave->expects($this->once())->method('delete')->willReturn('master');
        $replicate->delete('foo');

        $master->expects($this->once())->method('keys')->willReturn([]);
        $slave->expects($this->never())->method('keys')->willReturn([]);
        $this->assertInternalType('array', $replicate->keys());

        $master->expects($this->once())->method('exists')->willReturn(true);
        $slave->expects($this->never())->method('exists');
        $this->assertTrue($replicate->exists('foo'));

        $master->expects($this->once())->method('write')->willReturn(123);
        $slave->expects($this->once())->method('write')->willReturn(123);
        $this->assertTrue($replicate->write('foo', 'contents'));

        $master->expects($this->once())->method('read')->willReturn('master content');
        $slave->expects($this->never())->method('read');
        $this->assertSame('master content', $replicate->read('foo'));

        $master->expects($this->once())->method('rename');
        $slave->expects($this->once())->method('rename');
        $replicate->rename('foo', 'bar');

        $master->expects($this->once())->method('isDirectory');
        $slave->expects($this->never())->method('isDirectory');
        $replicate->isDirectory('foo');
    }
}
