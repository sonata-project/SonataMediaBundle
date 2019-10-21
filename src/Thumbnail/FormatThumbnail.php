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

namespace Sonata\MediaBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class FormatThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var ResizerInterface[]
     */
    private $resizers = [];

    /**
     * @param string $defaultFormat
     */
    public function __construct($defaultFormat)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * @param string $id
     */
    public function addResizer($id, ResizerInterface $resizer)
    {
        if (!isset($this->resizers[$id])) {
            $this->resizers[$id] = $resizer;
        }
    }

    /**
     * @param string $id
     *
     * @throws \Exception
     *
     * @return ResizerInterface
     */
    public function getResizer($id)
    {
        if (!isset($this->resizers[$id])) {
            throw new \LogicException(sprintf('Resizer with id: "%s" is not attached.', $id));
        }

        return $this->resizers[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $provider->getReferenceImage($media);
        } else {
            $path = sprintf('%s/thumb_%s_%s.%s', $provider->generatePath($media), $media->getId(), $format, $this->getExtension($media));
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $provider->getReferenceImage($media);
        }

        return sprintf(
            '%s/thumb_%s_%s.%s',
            $provider->generatePath($media),
            $media->getId(),
            $format,
            $this->getExtension($media)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        if (!$provider->requireThumbnails()) {
            return;
        }

        $referenceFile = $provider->getReferenceFile($media);

        if (!$referenceFile->exists()) {
            return;
        }

        foreach ($provider->getFormats() as $format => $settings) {
            if (substr($format, 0, \strlen($media->getContext())) === $media->getContext() ||
                MediaProviderInterface::FORMAT_ADMIN === $format) {
                $resizer = (isset($settings['resizer']) && ($settings['resizer'])) ?
                    $this->getResizer($settings['resizer']) :
                    $provider->getResizer();
                $resizer->resize(
                    $media,
                    $referenceFile,
                    $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format), true),
                    $this->getExtension($media),
                    $settings
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null)
    {
        if (null === $formats) {
            $formats = array_keys($provider->getFormats());
        } elseif (\is_string($formats)) {
            $formats = [$formats];
        }

        if (!\is_array($formats)) {
            throw new \InvalidArgumentException('"Formats" argument should be string or array');
        }

        foreach ($formats as $format) {
            $path = $provider->generatePrivateUrl($media, $format);
            if ($path && $provider->getFilesystem()->has($path)) {
                $provider->getFilesystem()->delete($path);
            }
        }
    }

    /**
     * @return string the file extension for the $media, or the $defaultExtension if not available
     */
    protected function getExtension(MediaInterface $media)
    {
        $ext = $media->getExtension();
        if (!\is_string($ext) || \strlen($ext) < 3) {
            $ext = $this->defaultFormat;
        }

        return $ext;
    }
}
