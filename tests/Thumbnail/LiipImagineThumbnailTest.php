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

use Gaufrette\Adapter\InMemory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;

class LiipImagineThumbnailTest extends TestCase
{
    public function testGenerate(): void
    {
        $cacheManager = $this->createStub(CacheManager::class);
        $cacheManager->method('getBrowserPath')->willReturn('cache/media/default/0011/24/ASDASDAS.png');

        $thumbnail = new LiipImagineThumbnail($cacheManager);

        $filesystem = new Filesystem(new InMemory(['myfile' => 'content']));
        $referenceFile = new File('myfile', $filesystem);

        $formats = [
            'admin' => ['height' => 50, 'width' => 50, 'quality' => 100],
            'mycontext_medium' => ['height' => 500, 'width' => 500, 'quality' => 100],
            'anothercontext_large' => ['height' => 500, 'width' => 500, 'quality' => 100],
        ];

        $media = new Media();
        $media->setName('ASDASDAS.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1_023_456);
        $media->setContext('default');

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->method('requireThumbnails')->willReturn(true);
        $provider->method('getReferenceFile')->willReturn($referenceFile);
        $provider->method('getFormats')->willReturn($formats);
        $provider->method('generatePrivateUrl')->willReturn('/my/private/path');
        $provider->method('generatePublicUrl')->willReturn('/my/public/path');
        $provider->method('getFilesystem')->willReturn($filesystem);
        $provider->method('getReferenceImage')->with($media)->willReturn('default/0011/24/ASDASDAS.png');
        $provider->method('getCdnPath')->with(
            'default/0011/24/ASDASDAS.png',
            null
        )->willReturn('cache/media/default/0011/24/ASDASDAS.png');

        static::assertSame('default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl(
            $provider,
            $media,
            MediaProviderInterface::FORMAT_ADMIN
        ));
        static::assertSame('cache/media/default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl(
            $provider,
            $media,
            'mycontext_medium'
        ));
    }
}
