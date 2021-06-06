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

use Aws\CloudFront\Exception\CloudFrontException;
use Aws\CommandInterface;
use Aws\Sdk;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\CloudFront;

/**
 * @todo Remove this class when support for aws/aws-sdk-php < 3.0 is dropped.
 *
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class CloudFrontTest extends TestCase
{
    protected function setUp(): void
    {
        if (class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires aws/aws-sdk-php 2.x.');
        }

        parent::setUp();
    }

    public function testCloudFront(): void
    {
        $client = $this->createMock(CloudFrontClientSpy::class);
        $cloudFront = new CloudFront($client, 'xxxxxxxxxxxxxx', '/foo');

        $this->assertSame('/foo/bar.jpg', $cloudFront->getPath('bar.jpg', true));

        $path = '/mypath/file.jpg';

        $client->expects($this->exactly(3))
            ->method('createInvalidation')
            ->willReturn(new CloudFrontResult([
                'Id' => 'invalidation_id',
                'Status' => 'InProgress',
            ]));

        $this->assertSame('invalidation_id', $cloudFront->flushByString($path));
        $this->assertSame('invalidation_id', $cloudFront->flush($path));
        $this->assertSame('invalidation_id', $cloudFront->flushPaths([$path]));

        $client->expects($this->once())
            ->method('getInvalidation')
            ->willReturn(new CloudFrontResult([
                'Id' => 'invalidation_id',
                'Status' => 'InProgress',
            ]));

        $this->assertSame(CloudFront::STATUS_WAITING, $cloudFront->getFlushStatus('invalidation_id'));
    }

    public function testCreateInvalidationException(): void
    {
        $client = $this->createMock(CloudFrontClientSpy::class);
        $cloudFront = new CloudFront($client, 'xxxxxxxxxxxxxx', '/foo');

        $client->expects($this->once())
            ->method('createInvalidation')
            ->willThrowException(new CloudFrontException('An exception occurred.', $this->createStub(CommandInterface::class)));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush paths "/bar", "/baz".');

        $cloudFront->flushPaths(['/bar', '/baz']);
    }
}

class CloudFrontResult
{
    private $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function get(string $key)
    {
        if (!\array_key_exists($key, $this->data)) {
            throw new \OutOfBoundsException('Invalid argument.');
        }

        return $this->data[$key];
    }
}
