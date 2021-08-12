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

use Aws\S3\Enum\Storage;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Mime\MimeTypesInterface;

final class AmazonMetadataBuilderTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testAmazon(array $settings, array $expected): void
    {
        $mimeTypes = $this->createStub(MimeTypesInterface::class);
        $mimeTypes->method('getMimeTypes')->willReturnCallback(static function (string $ext): array {
            return 'png' === $ext ? ['image/png'] : [];
        });

        $media = $this->createStub(MediaInterface::class);
        $filename = '/test/folder/testfile.png';

        $amazonmetadatabuilder = new AmazonMetadataBuilder($settings, $mimeTypes);
        self::assertSame($expected, $amazonmetadatabuilder->get($media, $filename));
    }

    public function provider(): iterable
    {
        yield [['acl' => 'private'], ['ACL' => AmazonMetadataBuilder::PRIVATE_ACCESS, 'contentType' => 'image/png']];
        yield [['acl' => 'public'], ['ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'contentType' => 'image/png']];
        yield [['acl' => 'open'], ['ACL' => AmazonMetadataBuilder::PUBLIC_READ_WRITE, 'contentType' => 'image/png']];
        yield [['acl' => 'auth_read'], ['ACL' => AmazonMetadataBuilder::AUTHENTICATED_READ, 'contentType' => 'image/png']];
        yield [['acl' => 'owner_read'], ['ACL' => AmazonMetadataBuilder::BUCKET_OWNER_READ, 'contentType' => 'image/png']];
        yield [['acl' => 'owner_full_control'], ['ACL' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL, 'contentType' => 'image/png']];
        yield [['storage' => 'standard'], ['storage' => AmazonMetadataBuilder::STORAGE_STANDARD, 'contentType' => 'image/png']];
        yield [['storage' => 'reduced'], ['storage' => AmazonMetadataBuilder::STORAGE_REDUCED, 'contentType' => 'image/png']];
        yield [['cache_control' => 'max-age=86400'], ['CacheControl' => 'max-age=86400', 'contentType' => 'image/png']];
        yield [['encryption' => 'aes256'], ['encryption' => 'AES256', 'contentType' => 'image/png']];
        yield [['meta' => ['key' => 'value']], ['meta' => ['key' => 'value'], 'contentType' => 'image/png']];
        yield [
            ['acl' => 'public', 'storage' => 'standard', 'cache_control' => 'max-age=86400', 'encryption' => 'aes256', 'meta' => ['key' => 'value']],
            [
                'ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'storage' => class_exists(Storage::class) ? Storage::STANDARD : AmazonMetadataBuilder::STORAGE_STANDARD,
                'meta' => ['key' => 'value'], 'CacheControl' => 'max-age=86400', 'encryption' => 'AES256', 'contentType' => 'image/png',
            ],
        ];
    }
}
