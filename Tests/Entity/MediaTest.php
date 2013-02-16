<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Entity;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    public function testMetadata()
    {

        $media = new Media;

        $media->setProviderMetadata(array('thumbnail_url' => 'http://pasloin.com/thumb.png'));

        $this->assertEquals($media->getMetadataValue('thumbnail_url'), 'http://pasloin.com/thumb.png', '::getMetadataValue() return the good value');
        $this->assertEquals($media->getMetadataValue('thumbnail_url1', 'default'), 'default', '::getMetadataValue() return the default');
        $this->assertEquals($media->getMetadataValue('thumbnail_url1'), null, '::getMetadataValue() return the null value');
    }

    public function testStatusList()
    {
        $status = Media::getStatusList();

        $this->assertInternalType('array', $status);
    }

    public function testSetGet()
    {
        $media = new Media;
        $media->setName('MediaBundle');
        $media->setSize(12);
        $media->setDescription('description');
        $media->setEnabled(true);
        $media->setProviderName('name');
        $media->setLength(2);
        $media->setCopyright('copyleft');
        $media->setAuthorName('Thomas');
        $media->setCdnIsFlushable(true);
        $media->setCdnFlushAt(new \DateTime);
        $media->setContentType('sonata/media');
        $media->setCreatedAt(new \DateTime);

        $this->assertEquals(12, $media->getSize());
        $this->assertEquals('description', $media->getDescription());
        $this->assertTrue($media->getEnabled());
        $this->assertEquals('name', $media->getProviderName());
        $this->assertEquals(2, $media->getLength());
        $this->assertEquals('copyleft', $media->getCopyright());
        $this->assertEquals('Thomas', $media->getAuthorName());
        $this->assertTrue($media->getCdnIsFlushable());
        $this->assertInstanceOf('DateTime', $media->getCdnFlushAt());
        $this->assertInstanceOf('DateTime', $media->getCreatedAt());
        $this->assertEquals('sonata/media', $media->getContentType());
        $this->assertEquals('MediaBundle', (string) $media);

        $this->assertNull($media->getMetadataValue('foo'));

    }

}
