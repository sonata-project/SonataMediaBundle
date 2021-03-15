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

namespace Sonata\MediaBundle\Provider;

use Gaufrette\Filesystem;
use Imagine\Image\Box;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

abstract class BaseVideoProvider extends BaseProvider
{
    /**
     * @var MetadataBuilderInterface
     */
    protected $metadata;

    /**
     * @var ClientInterface|null
     */
    private $client;

    /**
     * @var RequestFactoryInterface|null
     */
    private $requestFactory;

    public function __construct(
        string $name,
        Filesystem $filesystem,
        CDNInterface $cdn,
        GeneratorInterface $pathGenerator,
        ThumbnailInterface $thumbnail,
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        ?MetadataBuilderInterface $metadata = null
    ) {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        $this->metadata = $metadata;
        $this->requestFactory = $requestFactory;
        $this->client = $client;
    }

    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', ['class' => 'fa fa-video-camera']);
    }

    public function getReferenceImage(MediaInterface $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

    public function getReferenceFile(MediaInterface $media)
    {
        $key = $this->generatePrivateUrl($media, MediaProviderInterface::FORMAT_REFERENCE);

        // the reference file is remote, get it and store it with the 'reference' format
        if ($this->getFilesystem()->has($key)) {
            $referenceFile = $this->getFilesystem()->get($key);
        } else {
            $referenceFile = $this->getFilesystem()->get($key, true);
            $metadata = $this->metadata ? $this->metadata->get($media, $referenceFile->getName()) : [];

            $response = $this->sendRequest('GET', $this->getReferenceImage($media));

            $referenceFile->setContent($response, $metadata);
        }

        return $referenceFile;
    }

    public function generatePublicUrl(MediaInterface $media, $format)
    {
        return $this->getCdn()->getPath(sprintf(
            '%s/thumb_%s_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        ), $media->getCdnIsFlushable());
    }

    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return sprintf(
            '%s/thumb_%s_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        );
    }

    public function buildEditForm(FormMapper $formMapper): void
    {
        $formMapper->add('name');
        $formMapper->add('enabled', null, ['required' => false]);
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', TextType::class, ['required' => false]);
    }

    public function buildCreateForm(FormMapper $formMapper): void
    {
        $formMapper->add('binaryContent', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new NotNull(),
            ],
        ]);
    }

    public function buildMediaType(FormBuilder $formBuilder): void
    {
        $formBuilder->add('binaryContent', TextType::class, [
            'label' => 'widget_label_binary_content',
        ]);
    }

    public function postUpdate(MediaInterface $media): void
    {
        $this->postPersist($media);
    }

    public function postPersist(MediaInterface $media): void
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $this->generateThumbnails($media);

        $media->resetBinaryContent();
    }

    public function postRemove(MediaInterface $media): void
    {
    }

    /**
     * Get provider reference url.
     */
    abstract public function getReferenceUrl(MediaInterface $media): string;

    /**
     * @param string $url
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, $url)
    {
        try {
            $response = $this->sendRequest('GET', $url);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException('Unable to retrieve the video information for :'.$url, $e->getCode(), $e);
        }

        $metadata = json_decode($response, true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode the video information for :'.$url);
        }

        return $metadata;
    }

    /**
     * @param string $format
     * @param array  $options
     *
     * @return Box
     */
    protected function getBoxHelperProperties(MediaInterface $media, $format, $options = [])
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $media->getBox();
        }

        if (isset($options['width']) || isset($options['height'])) {
            $settings = [
                'width' => $options['width'] ?? null,
                'height' => $options['height'] ?? null,
            ];
        } else {
            $settings = $this->getFormat($format);
        }

        return $this->resizer->getBox($media, $settings);
    }

    /**
     * Creates an http request and sends it to the server.
     */
    final protected function sendRequest(string $method, string $url): string
    {
        return $this->client->sendRequest(
            $this->requestFactory->createRequest($method, $url)
        )->getBody()->getContents();
    }
}
