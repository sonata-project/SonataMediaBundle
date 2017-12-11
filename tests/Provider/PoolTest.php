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

namespace Sonata\MediaBundle\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
class PoolTest extends TestCase
{
    public function testGetEmptyProviderName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Provider name cannot be empty, did you forget to call setProviderName() in your Media object?');

        $mediaPool = $this
            ->getMockBuilder('Sonata\MediaBundle\Provider\Pool')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;

        $mediaPool->getProvider(null);
    }

    public function testGetWithEmptyProviders(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve provider named "provider_a" since there are no providers configured yet.');

        $mediaPool = $this
            ->getMockBuilder('Sonata\MediaBundle\Provider\Pool')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;

        $mediaPool->getProvider('provider_a');
    }

    public function testGetInvalidProviderName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to retrieve the provider named "provider_c". Available providers are "provider_a", "provider_b".');

        $mediaPool = $this
            ->getMockBuilder('Sonata\MediaBundle\Provider\Pool')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock()
        ;
        $mediaPool->setProviders([
            'provider_a' => $this->createProvider('provider_a'),
            'provider_b' => $this->createProvider('provider_b'),
        ]);
        $mediaPool->getProvider('provider_c');
    }

    /**
     * @param string $name
     *
     * @return FileProvider
     */
    protected function createProvider($name)
    {
        $filesystem = $this->getMockBuilder('Gaufrette\Filesystem')->disableOriginalConstructor()->getMock();
        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');
        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();
        $thumbnail = new FormatThumbnail('jpg');
        $metadata = $this->createMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        return new FileProvider($name, $filesystem, $cdn, $generator, $thumbnail, [], [], $metadata);
    }
}
