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

        $master->expects(static::once())->method('mtime')->willReturn(25);
        $slave->expects(static::never())->method('mtime');
        static::assertSame(25, $replicate->mtime('foo'));

        $master->expects(static::once())->method('delete')->willReturn('master');
        $slave->expects(static::once())->method('delete')->willReturn('master');
        $replicate->delete('foo');

        $master->expects(static::once())->method('keys')->willReturn([]);
        $slave->expects(static::never())->method('keys')->willReturn([]);
        static::assertIsArray($replicate->keys());

        $master->expects(static::once())->method('exists')->willReturn(true);
        $slave->expects(static::never())->method('exists');
        static::assertTrue($replicate->exists('foo'));

        $master->expects(static::once())->method('write')->willReturn(123);
        $slave->expects(static::once())->method('write')->willReturn(123);
        static::assertTrue($replicate->write('foo', 'contents'));

        $master->expects(static::once())->method('read')->willReturn('master content');
        $slave->expects(static::never())->method('read');
        static::assertSame('master content', $replicate->read('foo'));

        $master->expects(static::once())->method('rename');
        $slave->expects(static::once())->method('rename');
        $replicate->rename('foo', 'bar');

        $master->expects(static::once())->method('isDirectory');
        $slave->expects(static::never())->method('isDirectory');
        $replicate->isDirectory('foo');
    }
}
