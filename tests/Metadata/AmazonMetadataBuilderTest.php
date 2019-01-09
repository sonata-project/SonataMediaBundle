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

class AmazonMetadataBuilderTest extends TestCase
{
    public function testAmazon(): void
    {
        $media = $this->createMock(MediaInterface::class);
        $filename = '/test/folder/testfile.png';

        foreach ($this->provider() as $provider) {
            list($a, $b) = $provider;
            $amazonmetadatabuilder = new AmazonMetadataBuilder($a);
            $this->assertSame($b, $amazonmetadatabuilder->get($media, $filename));
        }
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            [['acl' => 'private'], ['ACL' => AmazonMetadataBuilder::PRIVATE_ACCESS, 'contentType' => 'image/png']],
            [['acl' => 'public'], ['ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'contentType' => 'image/png']],
            [['acl' => 'open'], ['ACL' => AmazonMetadataBuilder::PUBLIC_READ_WRITE, 'contentType' => 'image/png']],
            [['acl' => 'auth_read'], ['ACL' => AmazonMetadataBuilder::AUTHENTICATED_READ, 'contentType' => 'image/png']],
            [['acl' => 'owner_read'], ['ACL' => AmazonMetadataBuilder::BUCKET_OWNER_READ, 'contentType' => 'image/png']],
            [['acl' => 'owner_full_control'], ['ACL' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL, 'contentType' => 'image/png']],
            [['storage' => 'standard'], ['storage' => AmazonMetadataBuilder::STORAGE_STANDARD, 'contentType' => 'image/png']],
            [['storage' => 'reduced'], ['storage' => AmazonMetadataBuilder::STORAGE_REDUCED, 'contentType' => 'image/png']],
            [['cache_control' => 'max-age=86400'], ['CacheControl' => 'max-age=86400', 'contentType' => 'image/png']],
            [['encryption' => 'aes256'], ['encryption' => 'AES256', 'contentType' => 'image/png']],
            [['meta' => ['key' => 'value']], ['meta' => ['key' => 'value'], 'contentType' => 'image/png']],
            [['acl' => 'public', 'storage' => 'standard', 'cache_control' => 'max-age=86400', 'encryption' => 'aes256', 'meta' => ['key' => 'value']], ['ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'storage' => Storage::STANDARD, 'meta' => ['key' => 'value'], 'CacheControl' => 'max-age=86400', 'encryption' => 'AES256', 'contentType' => 'image/png']],
        ];
    }
}
