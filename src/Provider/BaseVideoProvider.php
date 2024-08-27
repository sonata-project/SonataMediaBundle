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

use Gaufrette\File;
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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

abstract class BaseVideoProvider extends BaseProvider
{
    public function __construct(
        string $name,
        Filesystem $filesystem,
        CDNInterface $cdn,
        GeneratorInterface $pathGenerator,
        ThumbnailInterface $thumbnail,
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        protected ?MetadataBuilderInterface $metadata = null
    ) {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);
    }

    public function getProviderMetadata(): MetadataInterface
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', ['class' => 'fa fa-video-camera']);
    }

    final public function getReferenceImage(MediaInterface $media): string
    {
        return $media->getMetadataValue('thumbnail_url');
    }

    final public function getReferenceFile(MediaInterface $media): File
    {
        $key = $this->generatePrivateUrl($media, MediaProviderInterface::FORMAT_REFERENCE);

        // the reference file is remote, get it and store it with the 'reference' format
        if ($this->getFilesystem()->has($key)) {
            $referenceFile = $this->getFilesystem()->get($key);
        } else {
            $referenceFile = $this->getFilesystem()->get($key, true);
            $metadata = null !== $this->metadata ? $this->metadata->get($media, $referenceFile->getName()) : [];

            $response = $this->sendRequest('GET', $this->getReferenceImage($media));

            $referenceFile->setContent($response, $metadata);
        }

        return $referenceFile;
    }

    final public function generatePublicUrl(MediaInterface $media, string $format): string
    {
        $id = $media->getId();

        if (null === $id) {
            throw new \InvalidArgumentException('Unable to generate public url for media without id.');
        }

        return $this->getCdn()->getPath(\sprintf(
            '%s/thumb_%s_%s.jpg',
            $this->generatePath($media),
            $id,
            $format
        ), $media->getCdnIsFlushable());
    }

    final public function generatePrivateUrl(MediaInterface $media, string $format): string
    {
        $id = $media->getId();

        if (null === $id) {
            throw new \InvalidArgumentException('Unable to generate public url for media without id.');
        }

        return \sprintf(
            '%s/thumb_%s_%s.jpg',
            $this->generatePath($media),
            $id,
            $format
        );
    }

    public function buildEditForm(FormMapper $form): void
    {
        $form->add('name');
        $form->add('enabled', null, ['required' => false]);
        $form->add('authorName');
        $form->add('cdnIsFlushable');
        $form->add('description');
        $form->add('copyright');
        $form->add('binaryContent', TextType::class, ['required' => false]);
    }

    public function buildCreateForm(FormMapper $form): void
    {
        $form->add('binaryContent', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new NotNull(),
            ],
        ]);
    }

    public function buildMediaType(FormBuilderInterface $formBuilder): void
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
        if (null === $media->getBinaryContent()) {
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
     *
     * @throws \InvalidArgumentException if $media reference url cannot be generated
     */
    abstract public function getReferenceUrl(MediaInterface $media): string;

    /**
     * @throws \RuntimeException
     *
     * @return mixed
     */
    protected function getMetadata(MediaInterface $media, string $url)
    {
        try {
            $response = $this->sendRequest('GET', $url);
        } catch (\RuntimeException $exception) {
            throw new \RuntimeException(
                \sprintf('Unable to retrieve the video information for: %s', $url),
                (int) $exception->getCode(),
                $exception
            );
        }

        $metadata = json_decode($response, true, 512, \JSON_THROW_ON_ERROR);

        if (null === $metadata) {
            throw new \RuntimeException(\sprintf('Unable to decode the video information for: %s', $url));
        }

        return $metadata;
    }

    /**
     * @param array<string, mixed> $options
     */
    protected function getBoxHelperProperties(MediaInterface $media, string $format, array $options = []): Box
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $media->getBox();
        }

        if (isset($options['width']) || isset($options['height'])) {
            $settings = [
                'width' => isset($options['width']) && \is_int($options['width']) ? $options['width'] : null,
                'height' => isset($options['height']) && \is_int($options['height']) ? $options['height'] : null,
                'quality' => 80,
                'format' => 'jpg',
                'constraint' => true,
                'resizer' => null,
                'resizer_options' => [],
            ];
        } else {
            $settings = $this->getFormat($format);

            if (false === $settings) {
                throw new \RuntimeException(\sprintf('Unable to retrieve format settings for format %s.', $format));
            }
        }

        if (null === $this->resizer) {
            throw new \RuntimeException('Resizer not set on the video provider.');
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
