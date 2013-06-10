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
use Sonata\AdminBundle\Model\ModelManagerInterface;

class FormatThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @param string $defaultFormat
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    public function __construct($defaultFormat, ModelManagerInterface $modelManager)
    {
        $this->defaultFormat = $defaultFormat;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $provider->getReferenceImage($media);
        } else {
            $path = sprintf('%s/thumb_%s_%s.%s',
                $provider->generatePath($media),
                $this->modelManager->getUrlsafeIdentifier($media),
                $format,
                $this->getExtension($media)
            );
        }

        if ($this->isExternalUrl($path)) {
            return $path;
        }

        $relativeWebPath = $provider->getRelativeWebPath();

        return $relativeWebPath ? sprintf('%s/%s', $relativeWebPath, $path) : $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        $path = sprintf('%s/thumb_%s_%s.%s',
            $provider->generatePath($media),
            $this->modelManager->getUrlsafeIdentifier($media),
            $format,
            $this->getExtension($media)
        );

        $relativeWebPath = $provider->getRelativeWebPath();

        return $relativeWebPath ? sprintf('%s/%s', $relativeWebPath, $path) : $path;
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
            if (substr($format, 0, strlen($media->getContext())) == $media->getContext() || $format === 'admin') {
                $path = $provider->generatePrivateUrl($media, $format);
                if (0 === strpos($path, $provider->getRelativeWebPath(), 0)) {
                    $path = ltrim(substr($path, strlen($provider->getRelativeWebPath())), '/');
                }

                $provider->getResizer()->resize(
                    $media,
                    $referenceFile,
                    $provider->getFilesystem()->get($path, true),
                    $this->getExtension($media),
                    $settings
                );
            }
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
            if ($path && $provider->getFilesystem()->has($path)) {
                $provider->getFilesystem()->delete($path);
            }
        }
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     *
     * @return string the file extension for the $media, or the $defaultExtension if not available
     */
    protected function getExtension(MediaInterface $media)
    {
        $ext = $media->getExtension();
        if (!is_string($ext) || strlen($ext) < 3) {
            $ext = $this->defaultFormat;
        }

        return $ext;
    }

    /**
     * @param $path
     * @return bool
     */
    protected function isExternalUrl($path)
    {
        return 0 === strpos($path, 'http', 0);
    }
}
