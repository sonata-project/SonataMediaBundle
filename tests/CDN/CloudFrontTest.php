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
use Aws\Sdk;
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
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx', 'us-west-1', '2020-05-31'])
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
        $client = $this->createMock(CloudFrontClientSpy::class);
        $client->expects($this->once())->method('createInvalidation')->willReturn(new CloudFrontResultSpy(true));
        $cloudFront = $this->getMockBuilder(CloudFront::class)
            ->setConstructorArgs(['/foo', 'secret', 'key', 'xxxxxxxxxxxxxx', 'us-west-1', '2020-05-31'])
            ->onlyMethods([])
            ->getMock();
        $cloudFront->setClient($client);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to flush : ');

        $cloudFront->flushPaths(['boom']);
    }

    /**
     * @todo: Remove this method when support for aws/aws-sdk-php < 3.0 is dropped.
     *
     * @dataProvider cloudFrontWithMissingArgumentsProvider
     *
     * @group legacy
     */
    public function testCloudFrontWithMissingArguments(string $expectedExceptionMessage, string $path, string $secret, string $key, string $distributionId, ?string $region = null, ?string $version = null): void
    {
        if (!class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires aws/aws-sdk-php 3.x.');
        }

        $client = $this->createMock(CloudFrontClientSpy::class);

        $client->expects($this->never())->method('createInvalidation')->willReturn(new CloudFrontResultSpy());

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        new CloudFront($path, $secret, $key, $distributionId, $region, $version);
    }

    public function cloudFrontWithMissingArgumentsProvider(): iterable
    {
        yield 'missing_argument_5' => [
            'Argument 5 for "Sonata\MediaBundle\CDN\CloudFront::__construct()" is required and can not be null when aws/aws-sdk-php >= 3.0 is installed.',
            '/foo', 'secret', 'key', 'xxxxxxxxxxxxxx',
        ];

        yield 'missing_argument_6' => [
            'Argument 6 for "Sonata\MediaBundle\CDN\CloudFront::__construct()" is required and can not be null when aws/aws-sdk-php >= 3.0 is installed.',
            '/foo', 'secret', 'key', 'xxxxxxxxxxxxxx', 'eu-west-3',
        ];
    }

    /**
     * @todo: Remove this method when support for aws/aws-sdk-php < 3.0 is dropped.
     *
     * @group legacy
     */
    public function testCloudFrontWithSdkV2(): void
    {
        if (class_exists(Sdk::class)) {
            $this->markTestSkipped('This test requires aws/aws-sdk-php 2.x.');
        }

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
