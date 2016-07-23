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

class AmazonMetadataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Aws\S3\Enum\CannedAcl')) {
            $this->markTestSkipped('Missing Aws\\S3\\Enum\\CannedAcl');
        }
    }

    public function testAmazon()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
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
        return array(
            array(array('acl' => 'private'), array('ACL' => AmazonMetadataBuilder::PRIVATE_ACCESS, 'contentType' => 'image/png')),
            array(array('acl' => 'public'), array('ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'open'), array('ACL' => AmazonMetadataBuilder::PUBLIC_READ_WRITE, 'contentType' => 'image/png')),
            array(array('acl' => 'auth_read'), array('ACL' => AmazonMetadataBuilder::AUTHENTICATED_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'owner_read'), array('ACL' => AmazonMetadataBuilder::BUCKET_OWNER_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'owner_full_control'), array('ACL' => AmazonMetadataBuilder::BUCKET_OWNER_FULL_CONTROL, 'contentType' => 'image/png')),
            array(array('storage' => 'standard'), array('storage' => AmazonMetadataBuilder::STORAGE_STANDARD, 'contentType' => 'image/png')),
            array(array('storage' => 'reduced'), array('storage' => AmazonMetadataBuilder::STORAGE_REDUCED, 'contentType' => 'image/png')),
            array(array('cache_control' => 'max-age=86400'), array('CacheControl' => 'max-age=86400', 'contentType' => 'image/png')),
            array(array('encryption' => 'aes256'), array('encryption' => 'AES256', 'contentType' => 'image/png')),
            array(array('meta' => array('key' => 'value')), array('meta' => array('key' => 'value'), 'contentType' => 'image/png')),
            array(array('acl' => 'public', 'storage' => 'standard', 'cache_control' => 'max-age=86400', 'encryption' => 'aes256', 'meta' => array('key' => 'value')), array('ACL' => AmazonMetadataBuilder::PUBLIC_READ, 'storage' => Storage::STANDARD, 'meta' => array('key' => 'value'), 'CacheControl' => 'max-age=86400', 'encryption' => 'AES256', 'contentType' => 'image/png')),
        );
    }
}
