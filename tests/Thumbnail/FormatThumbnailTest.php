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
        $resizer->expects($this->exactly(2))->method('resize')->will($this->returnValue(true));

        $provider = $this->createMock(MediaProviderInterface::class);
        $provider->expects($this->once())->method('requireThumbnails')->will($this->returnValue(true));
        $provider->expects($this->once())->method('getReferenceFile')->will($this->returnValue($referenceFile));
        $provider->expects($this->once())->method('getFormats')->will($this->returnValue($formats));
        $provider->expects($this->exactly(2))->method('getResizer')->will($this->returnValue($resizer));
        $provider->expects($this->exactly(2))->method('generatePrivateUrl')->will($this->returnValue('/my/private/path'));
        $provider->expects($this->exactly(2))->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->createMock(MediaInterface::class);
        $media->expects($this->exactly(6))->method('getContext')->will($this->returnValue('mycontext'));
        $media->expects($this->exactly(2))->method('getExtension')->will($this->returnValue('png'));

        $thumbnail->generate($provider, $media);
    }
}
