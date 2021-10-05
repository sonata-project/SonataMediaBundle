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

namespace Sonata\MediaBundle\Twig\Extension;

use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\InitRuntimeInterface;
use Twig\TwigFunction;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class MediaExtension extends AbstractExtension implements InitRuntimeInterface
{
    /**
     * @var Pool
     */
    protected $mediaService;

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * NEXT_MAJOR: Remove dependencies.
     */
    public function __construct(Pool $mediaService, ManagerInterface $mediaManager)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_media', [MediaRuntime::class, 'media']),
            new TwigFunction('sonata_thumbnail', [MediaRuntime::class, 'thumbnail']),
            new TwigFunction('sonata_path', [MediaRuntime::class, 'path']),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTokenParsers()
    {
        return [
            new MediaTokenParser(static::class),
            new ThumbnailTokenParser(static::class),
            new PathTokenParser(static::class),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function initRuntime(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function media($media, $format, $options = [])
    {
        @trigger_error(
            'Render media through media twig tag is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "sonata_media()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->mediaService,
            $this->mediaManager,
            $this->environment
        ))->media($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns the thumbnail for the provided media.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = [])
    {
        @trigger_error(
            'Render media through thumbnail twig tag is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "sonata_thumbnail()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->mediaService,
            $this->mediaManager,
            $this->environment
        ))->thumbnail($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param string $template
     *
     * @return mixed
     */
    public function render($template, array $parameters = [])
    {
        if (!isset($this->resources[$template])) {
            $this->resources[$template] = $this->environment->loadTemplate($template);
        }

        return $this->resources[$template]->render($parameters);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        @trigger_error(
            'Render media through path twig tag is deprecated since sonata-project/media-bundle 3.x and will be removed'
            .' in version 4.0. Use "sonata_path()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->mediaService,
            $this->mediaManager,
            $this->environment
        ))->path($media, $format);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return Pool
     */
    public function getMediaService()
    {
        return $this->mediaService;
    }
}
