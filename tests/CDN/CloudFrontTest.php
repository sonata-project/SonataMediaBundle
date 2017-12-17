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
        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->setMethods(['createInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->exactly(3))->method('createInvalidation')->will($this->returnValue(new CloudFrontResultSpy()));

        $cloudFront = $this->getMockBuilder(CloudFront::class)
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'])
            ->setMethods(null)
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

        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->setMethods(['createInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->exactly(1))->method('createInvalidation')->will($this->returnValue(new CloudFrontResultSpy(true)));
        $cloudFront = $this->getMockBuilder(CloudFront::class)
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx'])
            ->setMethods(null)
            ->getMock();
        $cloudFront->setClient($client);
        $cloudFront->flushPaths(['boom']);
    }
}

class CloudFrontResultSpy
{
    protected $fail = false;

    public function __construct($fail = false)
    {
        $this->fail = $fail;
    }

    public function get($data)
    {
        if ('Status' !== $data || $this->fail) {
            return;
        }

        return 'InProgress';
    }
}
