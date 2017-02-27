<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Entity;

use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class MediaTest extends PHPUnit_Framework_TestCase
{
    public function testMetadata()
    {
        $media = new Media();

        $media->setProviderMetadata(array('thumbnail_url' => 'http://pasloin.com/thumb.png'));

        $this->assertSame($media->getMetadataValue('thumbnail_url'), 'http://pasloin.com/thumb.png', '::getMetadataValue() return the good value');
        $this->assertSame($media->getMetadataValue('thumbnail_url1', 'default'), 'default', '::getMetadataValue() return the default');
        $this->assertSame($media->getMetadataValue('thumbnail_url1'), null, '::getMetadataValue() return the null value');
    }

    public function testStatusList()
    {
        $status = Media::getStatusList();

        $this->assertInternalType('array', $status);
    }

    public function testSetGet()
    {
        $category = $this->prophesize();
        $category->willExtend('Sonata\MediaBundle\Tests\Controller\EntityWithGetId');
        $category->willImplement('Sonata\ClassificationBundle\Model\CategoryInterface');

        $media = new Media();
        $media->setName('MediaBundle');
        $media->setSize(12);
        $media->setDescription('description');
        $media->setEnabled(true);
        $media->setProviderName('name');
        $media->setLength(2);
        $media->setCategory($category->reveal());
        $media->setCopyright('copyleft');
        $media->setAuthorName('Thomas');
        $media->setCdnIsFlushable(true);
        $media->setCdnFlushIdentifier('identifier_123');
        $media->setCdnFlushAt(new \DateTime());
        $media->setContentType('sonata/media');
        $media->setCreatedAt(new \DateTime());

        $this->assertSame(12, $media->getSize());
        $this->assertSame('description', $media->getDescription());
        $this->assertTrue($media->getEnabled());
        $this->assertSame('name', $media->getProviderName());
        $this->assertSame(2, $media->getLength());
        $this->assertSame($category->reveal(), $media->getCategory());
        $this->assertSame('copyleft', $media->getCopyright());
        $this->assertSame('Thomas', $media->getAuthorName());
        $this->assertTrue($media->getCdnIsFlushable());
        $this->assertSame('identifier_123', $media->getCdnFlushIdentifier());
        $this->assertInstanceOf('DateTime', $media->getCdnFlushAt());
        $this->assertInstanceOf('DateTime', $media->getCreatedAt());
        $this->assertSame('sonata/media', $media->getContentType());
        $this->assertSame('MediaBundle', (string) $media);

        $this->assertNull($media->getMetadataValue('foo'));
    }

    public function testGetMediaFileExtension()
    {
        $media = new Media();

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1');
        $this->assertSame('png', $media->getExtension(), 'extension should not contain query strings');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png#some-hash');
        $this->assertSame('png', $media->getExtension(), 'extension should not contain hashes');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1#with-some-hash');
        $this->assertSame('png', $media->getExtension(), 'extension should not contain query strings or hashes');
    }

    public function testSetCategoryWithoutAnActualCategory()
    {
        $this->expectException('\InvalidArgumentException');

        $media = new Media();

        $media->setCategory(new \stdClass());
    }
}
