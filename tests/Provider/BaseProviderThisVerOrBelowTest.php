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

class BaseProviderThisVerOrBelowTest extends AbstractProviderTest
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

        $cdn = new Server('/uploads/media');

        $generator = new DefaultGenerator();

        $thumbnail = $this->createMock(ThumbnailInterface::class);

        $metadata = $this->createMock(MetadataBuilderInterface::class);

        $provider = new TestBaseProvider('test', $filesystem, $cdn, $generator, $thumbnail, $metadata);
        $this->assertInstanceOf(BaseProvider::class, $provider);

        return $provider;
    }

    public function testPostRemove(): void
    {
        $provider = $this->getProvider();
        $media = new Media();
        
        //The ID is more than a thousand, the bug manifests itself under this condition.
        $media->setId(1399);
        $media->setProviderReference('1f981a048e7d8b671415d17e9633abc0059df394.png');
        
        $provider->preRemove($media);
        
        // Emulate an object detached from the EntityManager.
        $media->setId(null);
        $provider->postRemove($media);
    }
}

class TestBaseProvider extends BaseProvider
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
    public function getReferenceImage(MediaInterface $media): string
    {
        // A copy of the code from \Sonata\MediaBundle\Provider\FileProvider::getReferenceImage()
        $path = sprintf('%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
        
        // Test code.
        AbstractProviderTest::assertSame('/0001/02/1f981a048e7d8b671415d17e9633abc0059df394.png', $path);
        return $path;
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
