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
use Sonata\MediaBundle\Model\MediaInterface;
use \AmazonS3 as AmazonS3;

class AmazonMetadataBuilderTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        if (!class_exists('AmazonS3', true)) {
            $this->markTestSkipped('The class AmazonS3 does not exist');
        }
    }

    public function testAmazon()
    {
        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $filename = '/test/folder/testfile.png';

        foreach ($this->provider() as $provider ) {
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
            array(array('acl'=>'private'), array('acl' => AmazonS3::ACL_PRIVATE, 'contentType'=>'image/png')),
            array(array('acl'=>'public'), array('acl' => AmazonS3::ACL_PUBLIC, 'contentType'=>'image/png')),
            array(array('acl'=>'open'), array('acl' => AmazonS3::ACL_OPEN, 'contentType'=>'image/png')),
            array(array('acl'=>'auth_read'), array('acl' => AmazonS3::ACL_AUTH_READ, 'contentType'=>'image/png')),
            array(array('acl'=>'owner_read'), array('acl' => AmazonS3::ACL_OWNER_READ, 'contentType'=>'image/png')),
            array(array('acl'=>'owner_full_control'), array('acl' => AmazonS3::ACL_OWNER_FULL_CONTROL, 'contentType'=>'image/png')),
            array(array('storage'=>'standard'), array('storage' => AmazonS3::STORAGE_STANDARD, 'contentType'=>'image/png')),
            array(array('storage'=>'reduced'), array('storage' => AmazonS3::STORAGE_REDUCED, 'contentType'=>'image/png')),
            array(array('cache_control'=>'max-age=86400'), array('headers'=>array('Cache-Control' => 'max-age=86400'), 'contentType'=>'image/png')),
            array(array('encryption'=>'aes256'), array('encryption' => 'AES256', 'contentType'=>'image/png')),
            array(array('meta'=>array('key'=>'value')), array('meta'=>array('key'=>'value'), 'contentType'=>'image/png')),
            array(array('acl'=>'public','storage'=>'standard','cache_control'=>'max-age=86400','encryption'=>'aes256','meta'=>array('key'=>'value')), array('acl' => AmazonS3::ACL_PUBLIC, 'contentType'=>'image/png','storage' => AmazonS3::STORAGE_STANDARD,'headers'=>array('Cache-Control' => 'max-age=86400'),'encryption' => 'AES256','meta'=>array('key'=>'value')))
        );
    }
}
