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
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\CDN\Server;
use Sonata\MediaBundle\Generator\IdGenerator;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\BaseProvider;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Tests\Entity\Media;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\FormBuilder;

class BaseProviderTest extends AbstractProviderTest
{
    public function getProvider(): MediaProviderInterface
    {
        $adapter = $this->createMock(Adapter::class);

        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->onlyMethods(['get'])
            ->setConstructorArgs([$adapter])
            ->getMock();

        $cdn = new Server('/uploads/media');

        $generator = new IdGenerator();

        $thumbnail = $this->createMock(ThumbnailInterface::class);

        $provider = new TestProvider('test', $filesystem, $cdn, $generator, $thumbnail);
        $this->assertInstanceOf(BaseProvider::class, $provider);

        return $provider;
    }

    public function testBaseProvider(): void
    {
        $provider = $this->getProvider();
        $provider->setTemplates([
            'edit' => 'edit.twig',
        ]);

        $this->assertIsArray($provider->getTemplates());
        $this->assertSame('edit.twig', $provider->getTemplate('edit'));

        $this->assertInstanceOf(CDNInterface::class, $provider->getCdn());

        $provider->addFormat('small', []);

        $this->assertIsArray($provider->getFormat('small'));

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
        $this->assertNotNull($provider->getProviderMetadata()->getImage());
        $this->assertSame('fa fa-file', $provider->getProviderMetadata()->getOption('class'));
        $this->assertSame('SonataMediaBundle', $provider->getProviderMetadata()->getDomain());
    }

    public function testPostRemove(): void
    {
        $reflect = new \ReflectionClass(BaseProvider::class);
        $prop = $reflect->getProperty('clones');
        $prop->setAccessible(true);

        $provider = $this->getProvider();
        $media = new Media();
        $media->setId(1399);
        $media->setProviderReference('1f981a048e7d8b671415d17e9633abc0059df394.png');
        $hash = spl_object_hash($media);

        $provider->preRemove($media);

        $this->assertArrayHasKey($hash, $prop->getValue($provider));

        $media->setId(null); // Emulate an object detached from the EntityManager.
        $provider->postRemove($media);

        $this->assertArrayNotHasKey($hash, $prop->getValue($provider));
        $this->assertSame('/0001/02/1f981a048e7d8b671415d17e9633abc0059df394.png', $provider->prevReferenceImage);

        $prop->setAccessible(false);
    }
}

class TestProvider extends BaseProvider
{
    /**
     * @var string
     */
    public $prevReferenceImage;

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
        $form->add('foo');
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $form): void
    {
        $form->add('foo');
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
        $this->prevReferenceImage = sprintf(
            '%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );

        return $this->prevReferenceImage;
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
        $formBuilder->add('foo');
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
