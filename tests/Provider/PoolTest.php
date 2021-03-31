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

use Gaufrette\Filesystem;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Provider\Pool;
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
            ->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $mediaPool->getProvider(null);
    }

    public function testGetWithEmptyProviders(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to retrieve provider named "provider_a" since there are no providers configured yet.');

        $mediaPool = $this
            ->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $mediaPool->getProvider('provider_a');
    }

    public function testGetInvalidProviderName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to retrieve the provider named "provider_c". Available providers are "provider_a", "provider_b".');

        $mediaPool = $this
            ->getMockBuilder(Pool::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $mediaPool->setProviders([
            'provider_a' => $this->createProvider('provider_a'),
            'provider_b' => $this->createProvider('provider_b'),
        ]);
        $mediaPool->getProvider('provider_c');
    }

    protected function createProvider(string $name): MediaProviderInterface
    {
        $filesystem = $this->createMock(Filesystem::class);
        $cdn = new Server('/uploads/media');
        $generator = new IdGenerator();
        $thumbnail = new FormatThumbnail('jpg');
        $metadata = $this->createMock(MetadataBuilderInterface::class);

        return new FileProvider($name, $filesystem, $cdn, $generator, $thumbnail, [], [], $metadata);
    }
}
