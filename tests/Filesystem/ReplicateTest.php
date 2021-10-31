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
        $primary = $this->createMock(Adapter::class);
        $secondary = $this->createMock(Adapter::class);
        $replicate = new Replicate($primary, $secondary);

        $primary->expects(static::once())->method('mtime')->willReturn(25);
        $secondary->expects(static::never())->method('mtime');
        static::assertSame(25, $replicate->mtime('foo'));

        $primary->expects(static::once())->method('delete')->willReturn('primary');
        $secondary->expects(static::once())->method('delete')->willReturn('primary');
        $replicate->delete('foo');

        $primary->expects(static::once())->method('keys')->willReturn([]);
        $secondary->expects(static::never())->method('keys')->willReturn([]);
        static::assertIsArray($replicate->keys());

        $primary->expects(static::once())->method('exists')->willReturn(true);
        $secondary->expects(static::never())->method('exists');
        static::assertTrue($replicate->exists('foo'));

        $primary->expects(static::once())->method('write')->willReturn(123);
        $secondary->expects(static::once())->method('write')->willReturn(123);
        static::assertTrue($replicate->write('foo', 'contents'));

        $primary->expects(static::once())->method('read')->willReturn('primary content');
        $secondary->expects(static::never())->method('read');
        static::assertSame('primary content', $replicate->read('foo'));

        $primary->expects(static::once())->method('rename');
        $secondary->expects(static::once())->method('rename');
        $replicate->rename('foo', 'bar');

        $primary->expects(static::once())->method('isDirectory')->willReturn(true);
        $secondary->expects(static::never())->method('isDirectory');
        $replicate->isDirectory('foo');
    }
}
