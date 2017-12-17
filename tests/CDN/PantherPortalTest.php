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

namespace Sonata\MediaBundle\Tests\CDN;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\PantherPortal;

class PantherPortalTest extends TestCase
{
    public function testPortal(): void
    {
        $client = $this->createMock(ClientSpy::class);
        $client->expects($this->any())->method('flush')
            ->will($this->returnValue('Flush successfully submitted.'));

        $panther = new PantherPortal('/foo', 'login', 'pass', 42);
        $panther->setClient($client);

        $this->assertSame('/foo/bar.jpg', $panther->getPath('bar.jpg', true));

        $path = '/mypath/file.jpg';

        $panther->flushByString($path);
        $panther->flush($path);
        $panther->flushPaths([$path]);
    }

    public function testException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush : Failed!!');

        $client = $this->createMock(ClientSpy::class);
        $client->expects($this->exactly(1))->method('flush')->will($this->returnValue('Failed!!'));

        $panther = new PantherPortal('/foo', 'login', 'pass', 42);
        $panther->setClient($client);

        $panther->flushPaths(['boom']);
    }
}

class ClientSpy
{
    public function flush()
    {
        return 'flushed';
    }
}
