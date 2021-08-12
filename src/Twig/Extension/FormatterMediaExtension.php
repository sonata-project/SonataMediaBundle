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

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class FormatterMediaExtension extends AbstractExtension implements ExtensionInterface
{
    /**
     * @var MediaExtension
     */
    protected $twigExtension;

    /**
     * NEXT_MAJOR: Remove dependencies.
     */
    public function __construct(MediaExtension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * NEXT_MAJOR: return empty array.
     */
    public function getAllowedTags()
    {
        return [
            'media',
            'path',
            'thumbnail',
        ];
    }

    public function getAllowedMethods()
    {
        return [
            MediaInterface::class => [
                'getProviderReference',
            ],
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTokenParsers()
    {
        return [
            new MediaTokenParser(__CLASS__),
            new ThumbnailTokenParser(__CLASS__),
            new PathTokenParser(__CLASS__),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @return MediaExtension
     */
    public function getTwigExtension()
    {
        return $this->twigExtension;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function media($media, $format, $options = [])
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int    $media
     * @param string $format
     * @param array  $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = [])
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int    $media
     * @param string $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        return $this->getTwigExtension()->path($media, $format);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getNodeVisitors()
    {
        return $this->getTwigExtension()->getNodeVisitors();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getFilters()
    {
        return $this->getTwigExtension()->getFilters();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTests()
    {
        return $this->getTwigExtension()->getTests();
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('sonata_media', [MediaRuntime::class, 'media'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_thumbnail', [MediaRuntime::class, 'thumbnail'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_path', [MediaRuntime::class, 'path']),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getOperators()
    {
        return $this->getTwigExtension()->getOperators();
    }

    public function getAllowedFilters()
    {
        return [];
    }

    public function getAllowedFunctions()
    {
        return [
            'sonata_media',
            'sonata_thumbnail',
            'sonata_path',
        ];
    }

    public function getAllowedProperties()
    {
        return [];
    }
}
