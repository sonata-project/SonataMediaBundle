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

use Gaufrette\Adapter\InMemory;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Sonata\MediaBundle\Tests\Helpers\PHPUnit_Framework_TestCase;
use Sonata\MediaBundle\Thumbnail\LiipImagineThumbnail;

class LiipImagineThumbnailTest extends PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $cacheManager = $this->createMock('Liip\ImagineBundle\Imagine\Cache\CacheMenager');
        $thumbnail = new LiipImagineThumbnail($cacheManager);

        $filesystem = new Filesystem(new InMemory(array('myfile' => 'content')));
        $referenceFile = new File('myfile', $filesystem);

        $formats = array(
          'admin' => array('height' => 50, 'width' => 50, 'quality' => 100),
          'mycontext_medium' => array('height' => 500, 'width' => 500, 'quality' => 100),
          'anothercontext_large' => array('height' => 500, 'width' => 500, 'quality' => 100),
        );

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

        $media = $this->createMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->any())->method('getContext')->will($this->returnValue('mycontext'));
        $media->expects($this->any())->method('getExtension')->will($this->returnValue('png'));

        $thumbnail->generate($provider, $media);
    }
}
