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

namespace Sonata\MediaBundle\Tests\Media;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\Media;

class MediaTest extends TestCase
{
    public function testSetMetadataValue(): Media
    {
        $media = $this->getMedia(853);
        $metadataProperty = $this->getMediaPropertyReflection('providerMetadata');

        $media->setMetadataValue('name', 'value');
        $metadata = $metadataProperty->getValue($media);
        static::assertArrayHasKey('name', $metadata, 'name metadata should be stored in the empty array');
        static::assertSame('value', $metadata['name'], 'the string value should be returned');

        $cropData = [
            'x' => 10,
            'y' => 20,
            'width' => 500,
            'height' => 500,
        ];
        $media->setMetadataValue('crop', $cropData);
        $metadata = $metadataProperty->getValue($media);
        static::assertArrayHasKey('crop', $metadata, 'crop should be stored in the existing array');
        static::assertArrayHasKey('name', $metadata, 'name metadata should still be in the array');
        static::assertSame($cropData, $metadata['crop'], 'the crop data array should be returned');

        return $media;
    }

    /**
     * @depends testSetMetadataValue
     */
    public function testUnsetMetadataValue(Media $media): void
    {
        $metadataProperty = $this->getMediaPropertyReflection('providerMetadata');

        $media->unsetMetadataValue('crop');
        $metadata = $metadataProperty->getValue($media);
        static::assertArrayNotHasKey('crop', $metadata, 'crop should not be in the metadata');

        $media->unsetMetadataValue('name');
        $metadata = $metadataProperty->getValue($media);
        static::assertEmpty($metadata, 'crop should not be in the metadata');

        try {
            $media->unsetMetadataValue('bullshit');
        } catch (\InvalidArgumentException) {
            static::fail('an invalid key should be ignored');
        }
    }

    protected function getMediaPropertyReflection(string $propertyName): \ReflectionProperty
    {
        $rc = new \ReflectionClass(Media::class);
        $property = $rc->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    protected function getMedia(mixed $id): Media
    {
        $media = $this->getMockForAbstractClass(Media::class);
        $media
            ->method('getId')
            ->willReturn($id);

        return $media;
    }
}
