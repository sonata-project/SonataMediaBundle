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

final class FormatThumbnail implements ThumbnailInterface, ResizableThumbnailInterface, GenerableThumbnailInterface
{
    /**
     * @var array<string, ResizerInterface>
     */
    private array $resizers = [];

    public function __construct(private string $defaultFormat)
    {
    }

    public function addResizer(string $id, ResizerInterface $resizer): void
    {
        if (!isset($this->resizers[$id])) {
            $this->resizers[$id] = $resizer;
        }
    }

    public function hasResizer(string $id): bool
    {
        return isset($this->resizers[$id]);
    }

    public function getResizer(string $id): ResizerInterface
    {
        if (!isset($this->resizers[$id])) {
            throw new \LogicException(\sprintf('Resizer with id: "%s" is not attached.', $id));
        }

        return $this->resizers[$id];
    }

    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $provider->getReferenceImage($media);
        }

        $id = $media->getId();

        if (null === $id) {
            throw new \InvalidArgumentException('Unable to generate public url for image without id.');
        }

        return \sprintf('%s/thumb_%s_%s.%s', $provider->generatePath($media), $id, $format, $this->getExtension($media));
    }

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $provider->getReferenceImage($media);
        }

        $id = $media->getId();

        if (null === $id) {
            throw new \InvalidArgumentException('Unable to generate private url for image without id.');
        }

        return \sprintf('%s/thumb_%s_%s.%s', $provider->generatePath($media), $id, $format, $this->getExtension($media));
    }

    public function generate(MediaProviderInterface $provider, MediaInterface $media): void
    {
        if (!$provider->requireThumbnails()) {
            return;
        }

        $referenceFile = $provider->getReferenceFile($media);

        if (!$referenceFile->exists()) {
            return;
        }

        foreach ($provider->getFormats() as $format => $settings) {
            if (
                substr($format, 0, \strlen($media->getContext() ?? '')) === $media->getContext()
                || MediaProviderInterface::FORMAT_ADMIN === $format
            ) {
                $resizer = null !== $settings['resizer'] && $this->hasResizer($settings['resizer']) ?
                    $this->getResizer($settings['resizer']) :
                    $provider->getResizer();

                if (null === $resizer) {
                    continue;
                }

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

    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null): void
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
            if ($provider->getFilesystem()->has($path)) {
                $provider->getFilesystem()->delete($path);
            }
        }
    }

    /**
     * Returns the file extension for the $media, or the $defaultExtension if not available.
     */
    private function getExtension(MediaInterface $media): string
    {
        $ext = $media->getExtension();
        if (!\is_string($ext) || \strlen($ext) < 3) {
            $ext = $this->defaultFormat;
        }

        return $ext;
    }
}
