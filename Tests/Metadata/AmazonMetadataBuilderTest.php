<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Metadata;

use Sonata\MediaBundle\Metadata\AmazonMetadataBuilder;
use Aws\S3\Enum\CannedAcl;
use Aws\S3\Enum\Storage;

class AmazonMetadataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testAmazon()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $filename = '/test/folder/testfile.png';

        foreach ($this->provider() as $provider) {
            list($a, $b) = $provider;
            $amazonmetadatabuilder = new AmazonMetadataBuilder($a);
            $this->assertEquals($b, $amazonmetadatabuilder->get($media, $filename));
        }
    }

    /**
     * @return array
     */
    public function provider()
    {
        return array(
            array(array('acl' => 'private'), array('ACL' => CannedAcl::PRIVATE_ACCESS, 'contentType' => 'image/png')),
            array(array('acl' => 'public'), array('ACL' => CannedAcl::PUBLIC_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'open'), array('ACL' => CannedAcl::PUBLIC_READ_WRITE, 'contentType' => 'image/png')),
            array(array('acl' => 'auth_read'), array('ACL' => CannedAcl::AUTHENTICATED_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'owner_read'), array('ACL' => CannedAcl::BUCKET_OWNER_READ, 'contentType' => 'image/png')),
            array(array('acl' => 'owner_full_control'), array('ACL' => CannedAcl::BUCKET_OWNER_FULL_CONTROL, 'contentType' => 'image/png')),
            array(array('storage' => 'standard'), array('storage' => Storage::STANDARD, 'contentType' => 'image/png')),
            array(array('storage' => 'reduced'), array('storage' => Storage::REDUCED, 'contentType' => 'image/png')),
            array(array('cache_control' => 'max-age=86400'), array('headers' => array('Cache-Control' => 'max-age=86400'), 'contentType' => 'image/png')),
            array(array('encryption' => 'aes256'), array('encryption' => 'AES256', 'contentType' => 'image/png')),
            array(array('meta' => array('key' => 'value')), array('meta' => array('key' => 'value'), 'contentType' => 'image/png')),
            array(array('acl' => 'public', 'storage' => 'standard', 'cache_control' => 'max-age=86400', 'encryption' => 'aes256', 'meta' => array('key' => 'value')), array('ACL' => CannedAcl::PUBLIC_READ, 'contentType' => 'image/png', 'storage' => Storage::STANDARD, 'headers' => array('Cache-Control' => 'max-age=86400'), 'encryption' => 'AES256', 'meta' => array('key' => 'value')))
        );
    }
}
