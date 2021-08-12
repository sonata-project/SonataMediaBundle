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

        $master->expects(self::once())->method('mtime')->willReturn('master');
        $slave->expects(self::never())->method('mtime');
        self::assertSame('master', $replicate->mtime('foo'));

        $master->expects(self::once())->method('delete')->willReturn('master');
        $slave->expects(self::once())->method('delete')->willReturn('master');
        $replicate->delete('foo');

        $master->expects(self::once())->method('keys')->willReturn([]);
        $slave->expects(self::never())->method('keys')->willReturn([]);
        self::assertIsArray($replicate->keys());

        $master->expects(self::once())->method('exists')->willReturn(true);
        $slave->expects(self::never())->method('exists');
        self::assertTrue($replicate->exists('foo'));

        $master->expects(self::once())->method('write')->willReturn(123);
        $slave->expects(self::once())->method('write')->willReturn(123);
        self::assertTrue($replicate->write('foo', 'contents'));

        $master->expects(self::once())->method('read')->willReturn('master content');
        $slave->expects(self::never())->method('read');
        self::assertSame('master content', $replicate->read('foo'));

        $master->expects(self::once())->method('rename');
        $slave->expects(self::once())->method('rename');
        $replicate->rename('foo', 'bar');

        $master->expects(self::once())->method('isDirectory');
        $slave->expects(self::never())->method('isDirectory');
        $replicate->isDirectory('foo');
    }
}
