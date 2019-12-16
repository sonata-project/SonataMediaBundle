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

use Aws\CloudFront\CloudFrontClient;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\CloudFront;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class CloudFrontTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testLegacyCloudFront(): void
    {
        $client = $this->createMock(CloudFrontClientSpy::class);

        $client->expects($this->exactly(3))->method('createInvalidation')->willReturn(new CloudFrontResultSpy());

        $cloudFront = $this->getMockBuilder(CloudFront::class)
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'])
            ->onlyMethods([])
            ->getMock();
        $cloudFront->setClient($client);

        $this->assertSame('/foo/bar.jpg', $cloudFront->getPath('bar.jpg', true));

        $path = '/mypath/file.jpg';

        $cloudFront->flushByString($path);
        $cloudFront->flush($path);
        $cloudFront->flushPaths([$path]);
    }

    /**
     * @group legacy
     */
    public function testLegacyException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush : ');
        $client = $this->createMock(CloudFrontClientSpy::class);
        $client->expects($this->once())->method('createInvalidation')->willReturn(new CloudFrontResultSpy(true));
        $cloudFront = $this->getMockBuilder(CloudFront::class)
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'])
            ->onlyMethods([])
            ->getMock();
        $cloudFront->setClient($client);
        $cloudFront->flushPaths(['boom']);
    }
}

class CloudFrontClientSpy extends CloudFrontClient
{
    public function createInvalidation(): CloudFrontResultSpy
    {
        return new CloudFrontResultSpy();
    }
}

final class CloudFrontResultSpy
{
    private $fail;

    public function __construct(bool $fail = false)
    {
        $this->fail = $fail;
    }

    public function get($data): string
    {
        if ('Status' !== $data || $this->fail) {
            return '';
        }

        return 'InProgress';
    }
}
