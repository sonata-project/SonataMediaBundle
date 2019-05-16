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
use PHPUnit\Framework\TestCase;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Thumbnail\FormatThumbnail;

class FormatThumbnailTest extends TestCase
{
    public function testGenerate(): void
    {
        $thumbnail = new FormatThumbnail('foo');

        $filesystem = new Filesystem(new InMemory(['myfile' => 'content']));
        $referenceFile = new File('myfile', $filesystem);

        $formats = [
           'admin' => ['height' => 50, 'width' => 50, 'quality' => 100],
           'mycontext_medium' => ['height' => 500, 'width' => 500, 'quality' => 100],
           'anothercontext_large' => ['height' => 500, 'width' => 500, 'quality' => 100],
        ];

        $resizer = $this->createMock(ResizerInterface::class);
        $resizer->expects($this->exactly(2))->method('resize')->willReturn(true);

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('requireThumbnails')->willReturn(true);
        $provider->expects($this->once())->method('getReferenceFile')->willReturn($referenceFile);
        $provider->expects($this->once())->method('getFormats')->willReturn($formats);
        $provider->expects($this->exactly(2))->method('getResizer')->willReturn($resizer);
        $provider->expects($this->exactly(2))->method('generatePrivateUrl')->willReturn('/my/private/path');
        $provider->expects($this->exactly(2))->method('getFilesystem')->willReturn($filesystem);

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->exactly(6))->method('getContext')->willReturn('mycontext');
        $media->expects($this->exactly(2))->method('getExtension')->willReturn('png');

        $thumbnail->generate($provider, $media);
    }
}
