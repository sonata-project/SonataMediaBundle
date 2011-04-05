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

use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\AdminBundle\Form\FormMapper;

class BaseProviderTest extends \PHPUnit_Framework_TestCase
{

    public function getProvider()
    {

        $adapter = $this->getMock('Gaufrette\Filesystem\Adapter');

        $file = $this->getMock('Gaufrette\Filesystem\File', array(), array($adapter));

        $filesystem = $this->getMock('Gaufrette\Filesystem\Filesystem', array('get'), array($adapter));
        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/updoads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();
        
        $provider = new TestProvider('test', $filesystem, $cdn, $generator);

        return $provider;
    }

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

        $media = new \Sonata\MediaBundle\Tests\Entity\Media;
        $media->setContext('test');
        
        $this->assertEquals('admin', $provider->getFormatName($media, 'admin'));
        $this->assertEquals('reference', $provider->getFormatName($media, 'reference'));
        $this->assertEquals('test_small', $provider->getFormatName($media, 'small'));
        $this->assertEquals('test_small', $provider->getFormatName($media, 'test_small'));
    }
}


class TestProvider extends BaseProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     */
    function getHelperProperties(MediaInterface $media, $format)
    {
        // TODO: Implement getHelperProperties() method.
    }

    /**
     *
     * @param  $media
     * @return void
     */
    function postPersist(MediaInterface $media)
    {
        // TODO: Implement postPersist() method.
    }

    /**
     * build the related create form
     *
     */
    function buildEditForm(FormMapper $form)
    {
        // TODO: Implement buildEditForm() method.
    }

    /**
     * build the related create form
     *
     */
    function buildCreateForm(FormMapper $form)
    {
        // TODO: Implement buildCreateForm() method.
    }

    /**
     *
     * @param  $media
     * @return void
     */
    function postUpdate(MediaInterface $media)
    {
        // TODO: Implement postUpdate() method.
    }

    /**
     * return the absolute path of the reference image or the service provider reference
     *
     * @return void
     */
    function getAbsolutePath(MediaInterface $media)
    {
        // TODO: Implement getAbsolutePath() method.
    }

    /**
     * return the reference image of the media, can be the videa thumbnail or the original uploaded picture
     *
     * @return string to the reference image
     */
    function getReferenceImage(MediaInterface $media)
    {
        // TODO: Implement getReferenceImage() method.
    }
}