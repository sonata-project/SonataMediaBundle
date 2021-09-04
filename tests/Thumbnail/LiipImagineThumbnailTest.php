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

namespace Sonata\MediaBundle\Tests\Security;

use Gaufrette\Adapter\InMemory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;
use Symfony\Component\Routing\RouterInterface;

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

        $resizer = $this->createStub(ResizerInterface::class);
        $resizer->method('resize')->willReturn(true);

        $media = new Media();
        $media->setName('ASDASDAS.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        $provider = $this->createStub(MediaProviderInterface::class);
        $provider->method('requireThumbnails')->willReturn(true);
        $provider->method('getReferenceFile')->willReturn($referenceFile);
        $provider->method('getFormats')->willReturn($formats);
        $provider->method('getResizer')->willReturn($resizer);
        $provider->method('generatePrivateUrl')->willReturn('/my/private/path');
        $provider->method('generatePublicUrl')->willReturn('/my/public/path');
        $provider->method('getFilesystem')->willReturn($filesystem);
        $provider->method('getReferenceImage')->with($media)->willReturn('default/0011/24/ASDASDAS.png');
        $provider->method('getCdnPath')->with(
            'default/0011/24/ASDASDAS.png',
            null
        )->willReturn('cache/media/default/0011/24/ASDASDAS.png');

        $thumbnail->generate($provider, $media);
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

    /**
     * @group legacy
     * @expectedDeprecation Using an instance of Symfony\Component\Routing\RouterInterface is deprecated since version 3.3 and will be removed in 4.0. Use Liip\ImagineBundle\Imagine\Cache\CacheManager.
     */
    public function testLegacyGenerate(): void
    {
        $router = $this->createStub(RouterInterface::class);
        $router->method('generate')->with(
            '_imagine_medium',
            ['path' => '/some/path/42_medium.jpg']
        )->willReturn('/imagine/medium/some/path/42_medium.jpg');
        $thumbnail = new LiipImagineThumbnail($router);
        $provider = $this->createStub(MediaProviderInterface::class);
        $media = $this->createStub(MediaInterface::class);
        $media->method('getId')->willReturn(42);
        $media->method('getCdnIsFlushable')->willReturn(true);
        $format = 'medium';
        $provider->method('getReferenceImage')->with($media)->willReturn('/some/image.jpg');
        $provider->method('generatePath')->with($media)->willReturn('/some/path');
        $provider->method('getCdnPath')->with(
            '/imagine/medium/some/path/42_medium.jpg',
            true
        )->willReturn('some/cdn/path');
        static::assertSame(
            'some/cdn/path',
            $thumbnail->generatePublicUrl($provider, $media, $format)
        );
    }
}
