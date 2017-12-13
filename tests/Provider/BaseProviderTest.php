<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Tests\Provider;

use Gaufrette\Adapter;
use Gaufrette\File;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\DefaultGenerator;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\FormBuilder;

class BaseProviderTest extends AbstractProviderTest
{
    public function getProvider()
    {
        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();
        $file = $this->getMockBuilder(File::class)
            ->setConstructorArgs(['foo', $filesystem])
            ->getMock();

        $filesystem->expects($this->any())
            ->method('get')
            ->will($this->returnValue($file));

        $cdn = new Server('/uploads/media');

        $generator = new DefaultGenerator();

        $thumbnail = $this->createMock(ThumbnailInterface::class);

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail, $metadata);

        return $provider;
    }

    public function testBaseProvider()
    {
        $provider = $this->getProvider();
        $provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        $this->assertInternalType('array', $provider->getTemplates());
        $this->assertSame('edit.twig', $provider->getTemplate('edit'));

        $this->assertInstanceOf(CDNInterface::class, $provider->getCdn());

        $provider->addFormat('small', []);

        $this->assertInternalType('array', $provider->getFormat('small'));

        $media = new Media();
        $media->setContext('test');

        $this->assertSame('admin', $provider->getFormatName($media, 'admin'));
        $this->assertSame('reference', $provider->getFormatName($media, 'reference'));
        $this->assertSame('test_small', $provider->getFormatName($media, 'small'));
        $this->assertSame('test_small', $provider->getFormatName($media, 'test_small'));
    }

    public function testGetCdnPath()
    {
        $provider = $this->getProvider();
        $this->assertSame('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testMetadata()
    {
        $provider = $this->getProvider();

        $this->assertSame('test', $provider->getProviderMetadata()->getTitle());
        $this->assertSame('test.description', $provider->getProviderMetadata()->getDescription());
        $this->assertFalse($provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-file', $provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }
}

class TestProvider extends BaseProvider
{
    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = [])
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
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = [])
    {
        // TODO: Implement getDownloadResponse() method.
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

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        // TODO: Implement doTransform() method.
    }
}
