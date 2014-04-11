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

        $filesystem = $this->getMock('Gaufrette\Filesystem', array('get'), array($adapter));
        $file = $this->getMock('Gaufrette\File', array(), array('foo', $filesystem));

        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new \Sonata\MediaBundle\CDN\Server('/uploads/media');

        $generator = new \Sonata\MediaBundle\Generator\DefaultGenerator();

        $thumbnail = $this->getMock('Sonata\MediaBundle\Thumbnail\ThumbnailInterface');

        $metadata = $this->getMock('Sonata\MediaBundle\Metadata\MetadataBuilderInterface');

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail, $metadata);

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
        $this->assertEquals('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testMetadata()
    {
        $provider = $this->getProvider();

        $this->assertEquals("test", $provider->getProviderMetadata()->getTitle());
        $this->assertEquals("test.description", $provider->getProviderMetadata()->getDescription());
        $this->assertNull($provider->getProviderMetadata()->getImage());
        $this->assertEquals("fa fa-file", $provider->getProviderMetadata()->getOption('class'));
        $this->assertEquals("SonataMediaBundle", $provider->getProviderMetadata()->getDomain());
    }
}

class TestProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format)
    {
        // TODO: Implement getHelperProperties() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(MediaInterface $media)
    {
        // TODO: Implement postPersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form)
    {
        // TODO: Implement buildEditForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $form)
    {
        // TODO: Implement buildCreateForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(MediaInterface $media)
    {
        // TODO: Implement postUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(MediaInterface $media)
    {
        // TODO: Implement getAbsolutePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        // TODO: Implement getReferenceImage() method.
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
      // TODO: Implement generatePrivateUrl() method.
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
      // TODO: Implement generatePublicUrl() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceFile(MediaInterface $media)
    {
      // TODO: Implement getReferenceFile() method.
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(MediaInterface $media)
    {
        // TODO: Implement preUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(MediaInterface $media)
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(MediaInterface $media)
    {
        // TODO: Implement prePersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array())
    {
        // TODO: Implement getDownloadResponse() method.
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        // TODO: Implement doTransform() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildMediaType(FormBuilder $formBuilder)
    {
        // TODO: Implement buildMediaType() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        // TODO: Implement updateMetadata() method.
    }
}
