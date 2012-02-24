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
use Symfony\Component\Form\FormBuilder;

class BaseProviderTest extends \PHPUnit_Framework_TestCase
{
    public function getProvider()
    {
        $adapter = $this->getMock('Gaufrette\Adapter');

        $file = $this->getMock('Gaufrette\File', array(), array($adapter));

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/updoads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = $this->getMock('Sonata\MediaBundle\Thumbnail\ThumbnailInterface');

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail);

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

    public function testGetCdnPath()
    {
        $provider = $this->getProvider();
        $this->assertEquals('/updoads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }
}

class TestProvider extends BaseProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     */
    public function getHelperProperties(MediaInterface $media, $format)
    {
        // TODO: Implement getHelperProperties() method.
    }

    /**
     *
     * @param  $media
     * @return void
     */
    public function postPersist(MediaInterface $media)
    {
        // TODO: Implement postPersist() method.
    }

    /**
     * build the related create form
     *
     */
    public function buildEditForm(FormMapper $form)
    {
        // TODO: Implement buildEditForm() method.
    }

    /**
     * build the related create form
     *
     */
    public function buildCreateForm(FormMapper $form)
    {
        // TODO: Implement buildCreateForm() method.
    }

    /**
     *
     * @param  $media
     * @return void
     */
    public function postUpdate(MediaInterface $media)
    {
        // TODO: Implement postUpdate() method.
    }

    /**
     * return the absolute path of the reference image or the service provider reference
     *
     * @return void
     */
    public function getAbsolutePath(MediaInterface $media)
    {
        // TODO: Implement getAbsolutePath() method.
    }

    /**
     * return the reference image of the media, can be the videa thumbnail or the original uploaded picture
     *
     * @return string to the reference image
     */
    public function getReferenceImage(MediaInterface $media)
    {
        // TODO: Implement getReferenceImage() method.
    }

    /**
     * Generate the private path
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
      // TODO: Implement generatePrivateUrl() method.
    }

    /**
     * Generate the public path
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
      // TODO: Implement generatePublicUrl() method.
    }

    /**
     *
     * @return \Gaufrette\File
     */
    public function getReferenceFile(MediaInterface $media)
    {
      // TODO: Implement getReferenceFile() method.
    }

    /**
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preUpdate(MediaInterface $media)
    {
        // TODO: Implement preUpdate() method.
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function postRemove(MediaInterface $media)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function prePersist(MediaInterface $media)
    {
        // TODO: Implement prePersist() method.
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

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    protected function doTransform(MediaInterface $media)
    {
        // TODO: Implement doTransform() method.
    }

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @return void
     */
    function buildMediaType(FormBuilder $formBuilder)
    {
        // TODO: Implement buildMediaType() method.
    }

}
