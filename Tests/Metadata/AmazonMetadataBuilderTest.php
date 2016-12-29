<?php

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
use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class AmazonMetadataBuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Sonata\MediaBundle\Model\MediaInterface
     */
    private $media;

    protected function setUp()
    {
        $this->media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
    }

    /**
     * @dataProvider metadataProvider
     *
     * @param array $a
     * @param array $b
     */
    public function testAmazon(array $a, array $b)
    {
        $amazonMetadataBuilder = new AmazonMetadataBuilder($a);
        $this->assertSame($b, $amazonMetadataBuilder->get($this->media, '/test/folder/testfile.png'));
    }

    /**
     * @return array
     */
    public function metadataProvider()
    {
        return array(
            array(
                array('acl' => AmazonMetadataBuilder::PRIVATE_ACCESS),
                array('ACL' => AmazonMetadataBuilder::PRIVATE_ACCESS, 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::PUBLIC_READ),
                array('ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::PUBLIC_READ_WRITE),
                array('ACL' => AmazonMetadataBuilder::PUBLIC_READ_WRITE, 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::AUTHENTICATED_READ),
                array('ACL' => AmazonMetadataBuilder::AUTHENTICATED_READ, 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::BUCKET_OWNER_READ),
                array('ACL' => AmazonMetadataBuilder::BUCKET_OWNER_READ, 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL),
                array('ACL' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL, 'contentType' => 'image/png'),
            ),
            array(
                array('storage' => 'standard'),
                array('storage' => AmazonMetadataBuilder::STORAGE_STANDARD, 'contentType' => 'image/png'),
            ),
            array(
                array('storage' => 'reduced'),
                array('storage' => AmazonMetadataBuilder::STORAGE_REDUCED, 'contentType' => 'image/png'),
            ),
            array(
                array('cache_control' => 'max-age=86400'),
                array('CacheControl' => 'max-age=86400', 'contentType' => 'image/png'),
            ),
            array(
                array('encryption' => 'aes256'),
                array('encryption' => 'AES256', 'contentType' => 'image/png'),
            ),
            array(
                array('meta' => array('key' => 'value')),
                array('meta' => array('key' => 'value'), 'contentType' => 'image/png'),
            ),
            array(
                array('acl' => AmazonMetadataBuilder::PUBLIC_READ, 'storage' => 'standard', 'cache_control' => 'max-age=86400', 'encryption' => 'aes256', 'meta' => array('key' => 'value')),
                array('ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'storage' => Storage::STANDARD, 'meta' => array('key' => 'value'), 'CacheControl' => 'max-age=86400', 'encryption' => 'AES256', 'contentType' => 'image/png'),
            ),
        );
    }
}
