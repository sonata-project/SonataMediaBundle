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
use Imagine\Image\ImagineInterface;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class ImageProvider extends FileProvider
{
    /**
     * @var ImagineInterface
     */
    protected $imagineAdapter;

    /**
     * @param string                   $name
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions, array $allowedMimeTypes, ImagineInterface $adapter, MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail, $allowedExtensions, $allowedMimeTypes, $metadata);

        $this->imagineAdapter = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata(
            $this->getName(),
            $this->getName().'.description',
            null,
            'SonataMediaBundle',
            ['class' => 'fa fa-picture-o']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = [])
    {
        if (isset($options['srcset'], $options['picture'])) {
            throw new \LogicException("The 'srcset' and 'picture' options must not be used simultaneously.");
        }

        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $box = $media->getBox();
        } else {
            $resizerFormat = $this->getFormat($format);
            if (false === $resizerFormat) {
                throw new \RuntimeException(sprintf('The image format "%s" is not defined.
                        Is the format registered in your ``sonata_media`` configuration?', $format));
            }

            $box = $this->resizer->getBox($media, $resizerFormat);
        }

        $mediaWidth = $box->getWidth();

        $params = [
            'alt' => $media->getDescription() ?: $media->getName(),
            'title' => $media->getName(),
            'src' => $this->generatePublicUrl($media, $format),
            'width' => $mediaWidth,
            'height' => $box->getHeight(),
        ];

        if (isset($options['picture'])) {
            $pictureParams = [];
            foreach ($options['picture'] as $key => $pictureFormat) {
                $formatName = $this->getFormatName($media, $pictureFormat);
                $settings = $this->getFormat($formatName);
                $src = $this->generatePublicUrl($media, $formatName);
                $mediaQuery = \is_string($key)
                    ? $key
                    : sprintf('(max-width: %dpx)', $this->resizer->getBox($media, $settings)->getWidth());

                $pictureParams['source'][] = ['media' => $mediaQuery, 'srcset' => $src];
            }

            unset($options['picture']);
            $pictureParams['img'] = $params + $options;
            $params = ['picture' => $pictureParams];
        } elseif (MediaProviderInterface::FORMAT_ADMIN !== $format) {
            $srcSetFormats = $this->getFormats();

            if (isset($options['srcset']) && \is_array($options['srcset'])) {
                $srcSetFormats = [];
                foreach ($options['srcset'] as $srcSetFormat) {
                    $formatName = $this->getFormatName($media, $srcSetFormat);
                    $srcSetFormats[$formatName] = $this->getFormat($formatName);
                }
                unset($options['srcset']);

                // Make sure the requested format is also in the srcSetFormats
                if (!isset($srcSetFormats[$format])) {
                    $srcSetFormats[$format] = $this->getFormat($format);
                }
            }

            if (!isset($options['srcset'])) {
                $srcSet = [];

                foreach ($srcSetFormats as $providerFormat => $settings) {
                    // Check if format belongs to the current media's context
                    if (0 === strpos($providerFormat, $media->getContext())) {
                        $width = $this->resizer->getBox($media, $settings)->getWidth();

                        $srcSet[] = sprintf('%s %dw', $this->generatePublicUrl($media, $providerFormat), $width);
                    }
                }

                // The reference format is not in the formats list
                $srcSet[] = sprintf(
                    '%s %dw',
                    $this->generatePublicUrl($media, MediaProviderInterface::FORMAT_REFERENCE),
                    $media->getBox()->getWidth()
                );

                $params['srcset'] = implode(', ', $srcSet);
            }

            $params['sizes'] = sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $mediaWidth);
        }

        return array_merge($params, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = true)
    {
        try {
            if (!$media->getBinaryContent() instanceof \SplFileInfo) {
                // this is now optimized at all!!!
                $path = tempnam(sys_get_temp_dir(), 'sonata_update_metadata');
                $fileObject = new \SplFileObject($path, 'w');
                $fileObject->fwrite($this->getReferenceFile($media)->getContent());
            } else {
                $fileObject = $media->getBinaryContent();
            }

            $image = $this->imagineAdapter->open($fileObject->getPathname());
            $size = $image->getSize();

            $media->setSize($fileObject->getSize());
            $media->setWidth($size->getWidth());
            $media->setHeight($size->getHeight());
        } catch (\LogicException $e) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            $media->setSize(0);
            $media->setWidth(0);
            $media->setHeight(0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $this->getReferenceImage($media);
        } else {
            $path = $this->thumbnail->generatePublicUrl($this, $media, $format);
        }

        // if $path is already an url, no further action is required
        if (null !== parse_url($path, PHP_URL_SCHEME)) {
            return $path;
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return $this->thumbnail->generatePrivateUrl($this, $media, $format);
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        parent::doTransform($media);

        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();
        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getFilename();
        } else {
            // Should not happen, FileProvider should throw an exception in that case
            return;
        }

        if (!\in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->allowedExtensions, true)
            || !\in_array($media->getBinaryContent()->getMimeType(), $this->allowedMimeTypes, true)) {
            return;
        }

        try {
            $image = $this->imagineAdapter->open($media->getBinaryContent()->getPathname());
        } catch (\RuntimeException $e) {
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        $size = $image->getSize();

        $media->setWidth($size->getWidth());
        $media->setHeight($size->getHeight());

        $media->setProviderStatus(MediaInterface::STATUS_OK);
    }
}
