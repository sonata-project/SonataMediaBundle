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

namespace Sonata\MediaBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Sonata\ClassificationBundle\Model\Category;

class MediaTest extends TestCase
{
    public function testMetadata(): void
    {
        $media = new Media();

        $media->setProviderMetadata(['thumbnail_url' => 'http://pasloin.com/thumb.png']);

        static::assertSame($media->getMetadataValue('thumbnail_url'), 'http://pasloin.com/thumb.png', '::getMetadataValue() return the good value');
        static::assertSame($media->getMetadataValue('thumbnail_url1', 'default'), 'default', '::getMetadataValue() return the default');
        static::assertNull($media->getMetadataValue('thumbnail_url1'), '::getMetadataValue() return the null value');
    }

    public function testStatusList(): void
    {
        $status = Media::getStatusList();

        static::assertIsArray($status);
    }

    public function testSetGet(): void
    {
        $category = new Category();

        $media = new Media();
        $media->setName('MediaBundle');
        $media->setSize(12);
        $media->setDescription('description');
        $media->setEnabled(true);
        $media->setProviderName('name');
        $media->setLength(2);
        $media->setCategory($category);
        $media->setCopyright('copyleft');
        $media->setAuthorName('Thomas');
        $media->setCdnIsFlushable(true);
        $media->setCdnFlushIdentifier('identifier_123');
        $media->setCdnFlushAt(new \DateTime());
        $media->setContentType('sonata/media');
        $media->setCreatedAt(new \DateTime());

        static::assertSame(12, $media->getSize());
        static::assertSame('description', $media->getDescription());
        static::assertTrue($media->getEnabled());
        static::assertSame('name', $media->getProviderName());
        static::assertSame(2, $media->getLength());
        static::assertSame($category, $media->getCategory());
        static::assertSame('copyleft', $media->getCopyright());
        static::assertSame('Thomas', $media->getAuthorName());
        static::assertTrue($media->getCdnIsFlushable());
        static::assertSame('identifier_123', $media->getCdnFlushIdentifier());
        static::assertInstanceOf('DateTime', $media->getCdnFlushAt());
        static::assertInstanceOf('DateTime', $media->getCreatedAt());
        static::assertSame('sonata/media', $media->getContentType());
        static::assertSame('MediaBundle', (string) $media);

        static::assertNull($media->getMetadataValue('foo'));
    }

    public function testGetMediaFileExtension(): void
    {
        $media = new Media();

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1');
        static::assertSame('png', $media->getExtension(), 'extension should not contain query strings');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png#some-hash');
        static::assertSame('png', $media->getExtension(), 'extension should not contain hashes');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1#with-some-hash');
        static::assertSame('png', $media->getExtension(), 'extension should not contain query strings or hashes');
    }

    public function testSetCategoryWithoutAnActualCategory(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $media = new Media();

        $media->setCategory(new \stdClass());
    }
}
