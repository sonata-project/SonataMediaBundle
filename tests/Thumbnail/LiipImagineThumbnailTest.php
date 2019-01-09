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
        $cacheManager = $this->prophesize(CacheManager::class);
        $cacheManager->getBrowserPath()->willReturn('cache/media/default/0011/24/ASDASDAS.png');

        $thumbnail = new LiipImagineThumbnail($cacheManager);

        $filesystem = new Filesystem(new InMemory(['myfile' => 'content']));
        $referenceFile = new File('myfile', $filesystem);

        $formats = [
          'admin' => ['height' => 50, 'width' => 50, 'quality' => 100],
          'mycontext_medium' => ['height' => 500, 'width' => 500, 'quality' => 100],
          'anothercontext_large' => ['height' => 500, 'width' => 500, 'quality' => 100],
        ];

        $resizer = $this->prophesize(ResizerInterface::class);
        $resizer->resize()->willReturn(true);

        $media = new Media();
        $media->setName('ASDASDAS.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        $provider = $this->prophesize(MediaProviderInterface::class);
        $provider->requireThumbnails()->willReturn(true);
        $provider->getReferenceFile()->willReturn($referenceFile);
        $provider->getFormats()->willReturn($formats);
        $provider->getResizer()->willReturn($resizer);
        $provider->generatePrivateUrl()->willReturn('/my/private/path');
        $provider->generatePublicUrl()->willReturn('/my/public/path');
        $provider->getFilesystem()->willReturn($filesystem);
        $provider->getReferenceImage($media)->willReturn('default/0011/24/ASDASDAS.png');
        $provider->getCdnPath(
            'default/0011/24/ASDASDAS.png',
            null
        )->willReturn('cache/media/default/0011/24/ASDASDAS.png');

        $thumbnail->generate($provider->reveal(), $media);
        $this->assertSame('default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl(
            $provider->reveal(),
            $media,
            MediaProviderInterface::FORMAT_ADMIN
        ));
        $this->assertSame('cache/media/default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl(
            $provider->reveal(),
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
        $router = $this->prophesize(RouterInterface::class);
        $router->generate(
            '_imagine_medium',
            ['path' => '/some/path/42_medium.jpg']
        )->willReturn('/imagine/medium/some/path/42_medium.jpg');
        $thumbnail = new LiipImagineThumbnail($router->reveal());
        $provider = $this->prophesize(MediaProviderInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $media->getId()->willReturn(42);
        $media->getCdnIsFlushable()->willReturn(true);
        $format = 'medium';
        $provider->getReferenceImage($media->reveal())->willReturn('/some/image.jpg');
        $provider->generatePath($media->reveal())->willReturn('/some/path');
        $provider->getCdnPath(
            '/imagine/medium/some/path/42_medium.jpg',
            true
        )->willReturn('some/cdn/path');
        $this->assertSame(
            'some/cdn/path',
            $thumbnail->generatePublicUrl($provider->reveal(), $media->reveal(), $format)
        );
    }
}
