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
    public function testSetMetadataValue()
    {
        $media = $this->getMedia(853);
        $metadataProperty = $this->getMediaPropertyReflection('providerMetadata');

        $media->setMetadataValue('name', 'value');
        $metadata = $metadataProperty->getValue($media);
        $this->assertArrayHasKey('name', $metadata, 'name metadata should be stored in the empty array');
        $this->assertSame('value', $metadata['name'], 'the string value should be returned');

        $cropData = [
            'x' => 10,
            'y' => 20,
            'width' => 500,
            'height' => 500,
        ];
        $media->setMetadataValue('crop', $cropData);
        $metadata = $metadataProperty->getValue($media);
        $this->assertArrayHasKey('crop', $metadata, 'crop should be stored in the existing array');
        $this->assertArrayHasKey('name', $metadata, 'name metadata should still be in the array');
        $this->assertSame($cropData, $metadata['crop'], 'the crop data array should be returned');

        return $media;
    }

    /**
     * @depends testSetMetadataValue
     */
    public function testUnsetMetadataValue($media): void
    {
        $metadataProperty = $this->getMediaPropertyReflection('providerMetadata');

        $media->unsetMetadataValue('crop');
        $metadata = $metadataProperty->getValue($media);
        $this->assertArrayNotHasKey('crop', $metadata, 'crop should not be in the metadata');

        $media->unsetMetadataValue('name');
        $metadata = $metadataProperty->getValue($media);
        $this->assertEmpty($metadata, 'crop should not be in the metadata');

        try {
            $media->unsetMetadataValue('bullshit');
        } catch (InvalidArgumentException $expected) {
            $this->fail('an invalid key should be ignored');
        }
    }

    protected function getMediaPropertyReflection($propertyName)
    {
        $rc = new \ReflectionClass(Media::class);
        $property = $rc->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    protected function getMedia($id)
    {
        $media = $this->getMockForAbstractClass(Media::class);
        $media->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $media;
    }
}
