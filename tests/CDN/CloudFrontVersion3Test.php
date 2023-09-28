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
use Aws\CloudFront\Exception\CloudFrontException;
use Aws\Command;
use Aws\Result;
use Aws\Sdk;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\CloudFrontVersion3;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class CloudFrontVersion3Test extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Sdk::class)) {
            static::markTestSkipped('This test requires aws/aws-sdk-php 3.x.');
        }

        parent::setUp();
    }

    /**
     * @dataProvider provideCloudFrontCases
     */
    public function testCloudFront(
        string $expectedPath,
        string $path,
        string $relativePath,
        string $invalidationId,
        int $expectedStatus,
        string $invalidationStatus
    ): void {
        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->addMethods(['createInvalidation', 'getInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $cloudFront = new CloudFrontVersion3($client, 'xxxxxxxxxxxxxx', $path);

        static::assertSame($expectedPath, $cloudFront->getPath($relativePath, true));

        $flushPath = '/mypath/file.jpg';

        $client->expects(static::exactly(3))
            ->method('createInvalidation')
            ->willReturn(new Result([
                'Invalidation' => [
                    'Id' => $invalidationId,
                    'Status' => $invalidationStatus,
                ],
            ]));

        static::assertSame($invalidationId, $cloudFront->flushByString($flushPath));
        static::assertSame($invalidationId, $cloudFront->flush($flushPath));
        static::assertSame($invalidationId, $cloudFront->flushPaths([$flushPath]));

        $client->expects(static::once())
            ->method('getInvalidation')
            ->willReturn(new Result([
                'Invalidation' => [
                    'Id' => $invalidationId,
                    'Status' => $invalidationStatus,
                ],
            ]));

        static::assertSame($expectedStatus, $cloudFront->getFlushStatus($invalidationId));
    }

    /**
     * @phpstan-return iterable<array{string, string, string, string, int, string}>
     */
    public function provideCloudFrontCases(): iterable
    {
        yield ['/foo/bar.jpg', '/foo', '/bar.jpg', 'ivalidation_id_42', CloudFrontVersion3::STATUS_WAITING, 'InProgress'];
        yield ['/foo/bar.jpg', '/foo', 'bar.jpg', 'ivalidation_a', CloudFrontVersion3::STATUS_OK, 'Completed'];
    }

    public function testCreateInvalidationException(): void
    {
        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->addMethods(['createInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects(static::once())
            ->method('createInvalidation')
            ->willThrowException(new CloudFrontException('An exception occurred.', new Command('command_name')));

        $cloudFront = new CloudFrontVersion3($client, 'xxxxxxxxxxxxxx', '/foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush paths "/bar", "/baz".');

        $cloudFront->flushPaths(['/bar', '/baz']);
    }

    public function testNoStatusException(): void
    {
        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->addMethods(['createInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();
        $cloudFront = new CloudFrontVersion3($client, 'xxxxxxxxxxxxxx', '/foo');

        $client->expects(static::once())
            ->method('createInvalidation')
            ->willReturn(new Result([
                'Invalidation' => [
                    'Id' => 'invalidation_id',
                ],
            ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to find the flush status from the given response.');

        $cloudFront->flushPaths(['/boom']);
    }

    public function testUnknownStatusException(): void
    {
        $client = $this->getMockBuilder(CloudFrontClient::class)
            ->addMethods(['createInvalidation'])
            ->disableOriginalConstructor()
            ->getMock();
        $cloudFront = new CloudFrontVersion3($client, 'xxxxxxxxxxxxxx', '/foo');

        $client->expects(static::once())
            ->method('createInvalidation')
            ->willReturn(new Result([
                'Invalidation' => [
                    'Id' => 'invalidation_id',
                    'Status' => 'SomeUnknownStatus',
                ],
            ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine the flush status from the given response: "SomeUnknownStatus".');

        $cloudFront->flushPaths(['/boom']);
    }
}
