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
use Sonata\MediaBundle\Tests\App\Entity\Category;

class MediaTest extends TestCase
{
    public function testMetadata(): void
    {
        $media = new Media();

        $media->setProviderMetadata(['thumbnail_url' => 'http://pasloin.com/thumb.png']);

        self::assertSame($media->getMetadataValue('thumbnail_url'), 'http://pasloin.com/thumb.png', '::getMetadataValue() return the good value');
        self::assertSame($media->getMetadataValue('thumbnail_url1', 'default'), 'default', '::getMetadataValue() return the default');
        self::assertNull($media->getMetadataValue('thumbnail_url1'), '::getMetadataValue() return the null value');
    }

    public function testStatusList(): void
    {
        $status = Media::getStatusList();

        self::assertCount(5, $status);
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
        $media->setLength(2.0);
        $media->setCategory($category);
        $media->setCopyright('copyleft');
        $media->setAuthorName('Thomas');
        $media->setCdnIsFlushable(true);
        $media->setCdnFlushIdentifier('identifier_123');
        $media->setCdnFlushAt(new \DateTime());
        $media->setContentType('sonata/media');
        $media->setCreatedAt(new \DateTime());

        self::assertSame(12, $media->getSize());
        self::assertSame('description', $media->getDescription());
        self::assertTrue($media->getEnabled());
        self::assertSame('name', $media->getProviderName());
        self::assertSame(2.0, $media->getLength());
        self::assertSame($category, $media->getCategory());
        self::assertSame('copyleft', $media->getCopyright());
        self::assertSame('Thomas', $media->getAuthorName());
        self::assertTrue($media->getCdnIsFlushable());
        self::assertSame('identifier_123', $media->getCdnFlushIdentifier());
        self::assertInstanceOf(\DateTimeInterface::class, $media->getCdnFlushAt());
        self::assertInstanceOf(\DateTimeInterface::class, $media->getCreatedAt());
        self::assertSame('sonata/media', $media->getContentType());
        self::assertSame('MediaBundle', (string) $media);

        self::assertNull($media->getMetadataValue('foo'));
    }

    public function testGetMediaFileExtension(): void
    {
        $media = new Media();

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1');
        self::assertSame('png', $media->getExtension(), 'extension should not contain query strings');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png#some-hash');
        self::assertSame('png', $media->getExtension(), 'extension should not contain hashes');

        $media->setProviderReference('https://sonata-project.org/bundles/sonatageneral/images/logo-small.png?some-query-string=1#with-some-hash');
        self::assertSame('png', $media->getExtension(), 'extension should not contain query strings or hashes');
    }
}
