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

namespace Sonata\MediaBundle\Tests\Thumbnail;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Provider\FileProviderInterface;
use Sonata\MediaBundle\Tests\App\Entity\Media;
use Sonata\MediaBundle\Thumbnail\FileThumbnail;
use Symfony\Component\Asset\Packages;

final class FileThumbnailTest extends TestCase
{
    /**
     * @var MockObject&Packages
     */
    private MockObject $packages;

    private FileThumbnail $thumbnail;

    protected function setUp(): void
    {
        $this->packages = $this->createMock(Packages::class);

        $this->thumbnail = new FileThumbnail($this->packages);
    }

    public function testGeneratePublicUrl(): void
    {
        $provider = $this->createStub(FileProviderInterface::class);
        $media = new Media();

        $this->packages->method('getUrl')->with('bundles/sonatamedia/file.png')->willReturnArgument(0);

        $publicUrl = $this->thumbnail->generatePublicUrl($provider, $media, 'admin');

        static::assertSame('bundles/sonatamedia/file.png', $publicUrl);

        $publicUrl = $this->thumbnail->generatePublicUrl($provider, $media, 'random');

        static::assertSame('bundles/sonatamedia/file.png', $publicUrl);
    }

    public function testGeneratePrivateUrl(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Unable to generate private thumbnail url for media files.');

        $this->thumbnail->generatePrivateUrl($this->createStub(FileProviderInterface::class), new Media(), 'random');
    }
}
