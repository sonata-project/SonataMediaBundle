<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Security;

use Gaufrette\Adapter;
use Gaufrette\Adapter\InMemory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\DefaultGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;

class LiipImagineThumbnailTest extends TestCase
{
    public function getProvider($allowedExtensions = [], $allowedMimeTypes = [], $box = false, $cdnWithUrl = false)
    {
        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));
        if ($box) {
            $resizer->expects($this->any())->method('getBox')->will($box);
        }

        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(File::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();
        $filesystem->expects($this->any())->method('get')->will($this->returnValue($file));

        $cdn = new Server('/uploads/media');
        if ($cdnWithUrl) {
            $cdn = new Server('https://www.example.com/uploads/media');
        }
        $generator = new DefaultGenerator();

        $cacheManager = $this->createMock(CacheManager::class);
        $thumbnail = new LiipImagineThumbnail($cacheManager);

        $size = $this->createMock(BoxInterface::class);
        $size->expects($this->any())->method('getWidth')->will($this->returnValue(100));
        $size->expects($this->any())->method('getHeight')->will($this->returnValue(100));

        $image = $this->createMock(ImageInterface::class);
        $image->expects($this->any())->method('getSize')->will($this->returnValue($size));

        $adapter = $this->createMock(ImagineInterface::class);
        $adapter->expects($this->any())->method('open')->will($this->returnValue($image));

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new ImageProvider('file', $filesystem, $cdn, $generator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $adapter, $metadata);
        $provider->setResizer($resizer);

        return $provider;
    }

    public function testGenerate()
    {
        $cacheManager = $this->createMock(CacheManager::class);
        $cacheManager->expects($this->any())->method('getBrowserPath')->will($this->returnValue('cache/media/default/0011/24/ASDASDAS.png'));

        $thumbnail = new LiipImagineThumbnail($cacheManager);

        $filesystem = new Filesystem(new InMemory(['myfile' => 'content']));
        $referenceFile = new File('myfile', $filesystem);

        $formats = [
          'admin' => ['height' => 50, 'width' => 50, 'quality' => 100],
          'mycontext_medium' => ['height' => 500, 'width' => 500, 'quality' => 100],
          'anothercontext_large' => ['height' => 500, 'width' => 500, 'quality' => 100],
        ];

        $resizer = $this->createMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->any())->method('resize')->will($this->returnValue(true));

        $provider = $this->createMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->any())->method('requireThumbnails')->will($this->returnValue(true));
        $provider->expects($this->any())->method('getReferenceFile')->will($this->returnValue($referenceFile));
        $provider->expects($this->any())->method('getFormats')->will($this->returnValue($formats));
        $provider->expects($this->any())->method('getResizer')->will($this->returnValue($resizer));
        $provider->expects($this->any())->method('generatePrivateUrl')->will($this->returnValue('/my/private/path'));
        $provider->expects($this->any())->method('generatePublicUrl')->will($this->returnValue('/my/public/path'));
        $provider->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));
        $provider = $this->getProvider();
        $media = new Media();
        $media->setName('ASDASDAS.png');
        $media->setProviderReference('ASDASDAS.png');
        $media->setId(1023456);
        $media->setContext('default');

        $thumbnail->generate($provider, $media);
        $this->assertSame('default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl($provider, $media, MediaProviderInterface::FORMAT_ADMIN));
        $this->assertSame('cache/media/default/0011/24/ASDASDAS.png', $thumbnail->generatePublicUrl($provider, $media, 'mycontext_medium'));
    }
}
