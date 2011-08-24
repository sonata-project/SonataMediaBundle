<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

abstract class ProviderTestCommon extends \PHPUnit_Framework_TestCase
{
    protected $filesystem;

    protected function getFilesystem($dirs = array())
    {
        if (!$this->filesystem) {
            $adapter = new \Gaufrette\Adapter\InMemory($dirs);
            $this->filesystem = new \Gaufrette\Filesystem($adapter);
        }
        return $this->filesystem;
    }

    protected function getMedia($id)
    {
        $media = $this->getMockForAbstractClass('Sonata\MediaBundle\Model\Media');
        $media->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
        return $media;
    }

    protected function getProvider()
    {
        $filesystem = $this->getFilesystem();
        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');
        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();
        $provider = $this->getMockForAbstractClass($this->provider, array('test', $filesystem, $cdn, $generator));

        return $provider;
    }
}