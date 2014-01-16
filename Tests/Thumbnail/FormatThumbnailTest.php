<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Security;

use Sonata\MediaBundle\Thumbnail\FormatThumbnail;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Gaufrette\Adapter\InMemory;
use Sonata\MediaBundle\Resizer\ResizerInterface;

class FormatThumbnailTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $thumbnail = new FormatThumbnail('foo');

        $filesystem = new Filesystem(new InMemory(array('myfile' => 'content')));
        $referenceFile = new File('myfile', $filesystem);

        $formats = array(
           'admin' => array('height' => 50, 'width' => 50, 'quality' => 100),
           'mycontext_medium' => array('height' => 500, 'width' => 500, 'quality' => 100),
           'anothercontext_large' => array('height' => 500, 'width' => 500, 'quality' => 100),
        );

        $resizer = $this->getMock('Sonata\MediaBundle\Resizer\ResizerInterface');
        $resizer->expects($this->exactly(2))->method('resize')->will($this->returnValue(true));

        $provider = $this->getMock('Sonata\MediaBundle\Provider\MediaProviderInterface');
        $provider->expects($this->once())->method('requireThumbnails')->will($this->returnValue(true));
        $provider->expects($this->once())->method('getReferenceFile')->will($this->returnValue($referenceFile));
        $provider->expects($this->once())->method('getFormats')->will($this->returnValue($formats));
        $provider->expects($this->exactly(2))->method('getResizer')->will($this->returnValue($resizer));
        $provider->expects($this->exactly(2))->method('generatePrivateUrl')->will($this->returnValue('/my/private/path'));
        $provider->expects($this->exactly(2))->method('getFilesystem')->will($this->returnValue($filesystem));

        $media = $this->getMock('Sonata\MediaBundle\Model\MediaInterface');
        $media->expects($this->exactly(6))->method('getContext')->will($this->returnValue('mycontext'));
        $media->expects($this->exactly(2))->method('getExtension')->will($this->returnValue('png'));

        $thumbnail->generate($provider, $media);
    }
}
