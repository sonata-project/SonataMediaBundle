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
    /**
     * @requires extension soap
     */
    public function testPortal(): void
    {
        $client = $this->createMock(ClientSpy::class);
        $client->expects(self::exactly(3))->method('flush')->willReturn('Flush successfully submitted.');

        $panther = new PantherPortal('/foo', 'login', 'pass', '42');
        $panther->setClient($client);

        self::assertSame('/foo/bar.jpg', $panther->getPath('bar.jpg', true));

        $path = '/mypath/file.jpg';

        $panther->flushByString($path);
        $panther->flush($path);
        $panther->flushPaths([$path]);
    }

    /**
     * @requires extension soap
     */
    public function testException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush: Failed!!');

        $client = $this->createMock(ClientSpy::class);
        $client->expects(self::once())->method('flush')->willReturn('Failed!!');

        $panther = new PantherPortal('/foo', 'login', 'pass', '42');
        $panther->setClient($client);

        $panther->flushPaths(['boom']);
    }
}

if (class_exists(\SoapClient::class)) {
    class ClientSpy extends \SoapClient
    {
        public function flush(): string
        {
            return 'hello';
        }
    }
}
