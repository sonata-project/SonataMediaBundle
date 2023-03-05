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

namespace Sonata\MediaBundle\Tests\Metadata;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @phpstan-import-type AmazonSettings from AmazonMetadataBuilder
 */
final class AmazonMetadataBuilderTest extends TestCase
{
    /**
     * @dataProvider provider
     *
     * @param array<string, string|int> $mediaAttributes
     * @param array<string, mixed>      $expected
     *
     * @phpstan-param AmazonSettings $settings
     */
    public function testAmazon(array $settings, array $mediaAttributes, array $expected): void
    {
        $mimeTypes = $this->createStub(MimeTypesInterface::class);
        $mimeTypes->method('getMimeTypes')->willReturnCallback(
            static fn (string $ext): array => 'png' === $ext ? ['image/png'] : []
        );

        $media = $this->createStub(MediaInterface::class);
        foreach ($mediaAttributes as $attribute => $value) {
            $media->method('get'.ucfirst($attribute))->willReturn($value);
        }
        $filename = '/test/folder/testfile.png';

        $amazonmetadatabuilder = new AmazonMetadataBuilder($settings, $mimeTypes);
        static::assertSame($expected, $amazonmetadatabuilder->get($media, $filename));
    }

    /**
     * @phpstan-return iterable<array{AmazonSettings, array<string, string|int>, array<string, mixed>}>
     */
    public function provider(): iterable
    {
        yield [
            [
                'acl' => 'private',
                'storage' => 'standard',
                'cache_control' => '',
                'encryption' => 'aes256',
                'meta' => [],
            ],
            [
                'size' => 3000,
            ],
            [
                'ACL' => AmazonMetadataBuilder::PRIVATE_ACCESS,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => [],
                'CacheControl' => '',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
                'contentLength' => 3000,
            ],
        ];

        yield [
            [
                'acl' => 'open',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ_WRITE,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'auth_read',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::AUTHENTICATED_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'owner_read',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::BUCKET_OWNER_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'owner_full_control',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'reduced',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_REDUCED,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];

        yield [
            [
                'acl' => 'public',
                'storage' => 'standard',
                'cache_control' => 'max-age=86400',
                'encryption' => 'aes256',
                'meta' => ['key' => 'value'],
            ],
            [],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ,
                'storage' => AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'],
                'CacheControl' => 'max-age=86400',
                'encryption' => 'AES256',
                'contentType' => 'image/png',
            ],
        ];
    }
}
