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

    public function testBaseProvider(): void
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

    public function testGetCdnPath(): void
    {
        $provider = $this->getProvider();
        $this->assertSame('/uploads/media/my_file.txt', $provider->getCdnPath('my_file.txt', false));
    }

    public function testMetadata(): void
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
    public function getHelperProperties(MediaInterface $media, $format, $options = []): void
    {
        // TODO: Implement getHelperProperties() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(MediaInterface $media): void
    {
        // TODO: Implement postPersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $form): void
    {
        // TODO: Implement buildEditForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $form): void
    {
        // TODO: Implement buildCreateForm() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(MediaInterface $media): void
    {
        // TODO: Implement postUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(MediaInterface $media): void
    {
        // TODO: Implement getAbsolutePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media): void
    {
        // TODO: Implement getReferenceImage() method.
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format): void
    {
        // TODO: Implement generatePrivateUrl() method.
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format): void
    {
        // TODO: Implement generatePublicUrl() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceFile(MediaInterface $media): void
    {
        // TODO: Implement getReferenceFile() method.
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(MediaInterface $media): void
    {
        // TODO: Implement preUpdate() method.
    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(MediaInterface $media): void
    {
        // TODO: Implement postRemove() method.
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(MediaInterface $media): void
    {
        // TODO: Implement prePersist() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = []): void
    {
        // TODO: Implement getDownloadResponse() method.
    }

    /**
     * {@inheritdoc}
     */
    public function buildMediaType(FormBuilder $formBuilder): void
    {
        // TODO: Implement buildMediaType() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false): void
    {
        // TODO: Implement updateMetadata() method.
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media): void
    {
        // TODO: Implement doTransform() method.
    }
}
