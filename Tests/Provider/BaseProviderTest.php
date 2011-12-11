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

use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\AdminBundle\Form\FormMapper;

class BaseProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testBaseProvider()
    {
        $provider = $this->getProvider();
        $provider->setTemplates(array(
            'edit' => 'edit.twig'
        ));

        $this->assertInternalType('array', $provider->getTemplates());
        $this->assertEquals('edit.twig', $provider->getTemplate('edit'));

        $this->assertInstanceOf('\Sonata\MediaBundle\CDN\CDNInterface', $provider->getCdn());

        $provider->addFormat('small', array());

        $this->assertInternalType('array', $provider->getFormat('small'));

        $media = $this->getMedia();
        $media->setContext('test');

        $this->assertEquals('admin', $provider->getFormatName($media, 'admin'));
        $this->assertEquals('reference', $provider->getFormatName($media, 'reference'));
        $this->assertEquals('test_small', $provider->getFormatName($media, 'small'));
        $this->assertEquals('test_small', $provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath()
    {
        $provider = $this->getProvider();
        $this->assertEquals('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testGenerateThumbnail()
    {
        $this->markTestIncomplete('need to test reference file passed in and using default reference file');
    }
    
    protected function getProvider()
    {
        $resizer = $this->getMock('Sonata\MediaBundle\Media\ResizerInterface', array('resize'));
        $resizer->expects($this->any())
            ->method('resize')
            ->will($this->returnValue(true));

        $adapter = $this->getMock('Gaufrette\Adapter');

        $file = $this->getMock('Gaufrette\File', array(), array($adapter));

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $provider = $this->getMockForAbstractClass('Sonata\MediaBundle\Provider\BaseProvider', array('file', $filesystem, $cdn, $generator));
        $provider->setResizer($resizer);

        return $provider;
    }

    protected function getMedia($id = null)
    {
        $media = $this->getMockForAbstractClass('Sonata\MediaBundle\Model\Media');
        $media->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $media;
    }

    /**
     * Mode can be x-file
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $format
     * @param $mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function getDownloadResponse(MediaInterface $media, $format, $mode)
    {
        // TODO: Implement getDownloadResponse() method.
    }


}
