<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

class FormatThumbnail implements ThumbnailInterface
{
    /**
     * {@inheritdoc}
     */
    protected function generatePath(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        $settings = $provider->getFormat($format);

        return sprintf('%s/thumb_%s_%s.%s',
            $provider->generatePath($media),
            $media->getId(),
            $format,
            isset($settings['format']) ? $settings['format'] : 'jpg'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $provider->getReferenceImage($media);
        } else {
            $path = $this->generatePath($provider, $media, $format);
        }

        return $provider->getCdnPath($path, $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        return $this->generatePath($provider, $media, $format);
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

        foreach ($provider->getFormats() as $format => $settings) {
            $provider->getResizer()->resize(
                $media,
                $referenceFile,
                $provider->getFilesystem()->get($provider->generatePrivateUrl($media, $format), true),
                isset($settings['format']) ? $settings['format'] : 'jpg',
                $settings
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media)
    {
        // delete the differents formats
        foreach ($provider->getFormats() as $format => $definition) {
            $path = $provider->generatePrivateUrl($media, $format);
            if ($provider->getFilesystem()->has($path)) {
                $provider->getFilesystem()->delete($path);
            }
        }
    }
}
