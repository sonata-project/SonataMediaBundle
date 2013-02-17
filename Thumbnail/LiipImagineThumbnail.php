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
use Symfony\Component\Routing\RouterInterface;

class LiipImagineThumbnail implements ThumbnailInterface
{
    /**
     * @var string
     */
    private $defaultFormat;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    protected $modelManager;

    /**
     * @param string $defaultFormat
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Sonata\AdminBundle\Model\ModelManagerInterface
     */
    public function __construct($defaultFormat, RouterInterface $router, ModelManagerInterface $modelManager)
    {
        $this->defaultFormat = $defaultFormat;
        $this->router = $router;
        $this->modelManager = $modelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $relativeWebPath = $provider->getRelativeWebPath();
            if ($relativeWebPath) {
                $path = sprintf('%s/%s', $relativeWebPath, $provider->getReferenceImage($media));
            } else {
                $path = $provider->getReferenceImage($media);
            }
        } else {
            $path = $this->router->generate(
                sprintf('_imagine_%s', $format),
                array('path' => sprintf('%s/img.%s',
                    $this->modelManager->getUrlsafeIdentifier($media),
                    $this->getExtension($media))
                )
            );
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, $format)
    {
        if ($format != 'reference') {
            throw new \RuntimeException('No private url for LiipImagineThumbnail');
        }

        $relativeWebPath = $provider->getRelativeWebPath();
        if ($relativeWebPath) {
            $path = sprintf('%s/%s', $relativeWebPath, $provider->getReferenceImage($media));
        } else {
            $path = $provider->getReferenceImage($media);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(MediaProviderInterface $provider, MediaInterface $media)
    {
        // nothing to generate, as generated on demand
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media)
    {
        // feature not available
        return;
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
}
