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
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;

final class FormatterMediaExtension extends AbstractExtension implements ExtensionInterface
{
    /**
     * @var MediaExtension
     */
    private $twigExtension;

    public function __construct(MediaExtension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    public function getAllowedTags(): array
    {
        return [
            'media',
            'path',
            'thumbnail',
        ];
    }

    public function getAllowedMethods(): array
    {
        return [
            MediaInterface::class => [
                'getproviderreference',
            ],
        ];
    }

    public function getTokenParsers(): array
    {
        return [
            new MediaTokenParser(__CLASS__),
            new ThumbnailTokenParser(__CLASS__),
            new PathTokenParser(__CLASS__),
        ];
    }

    public function getTwigExtension(): MediaExtension
    {
        return $this->twigExtension;
    }

    /**
     * @param int|string $media
     * @param string     $format
     * @param array      $options
     *
     * @return string
     */
    public function media($media, $format, $options = [])
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * @param int|string $media
     * @param string     $format
     * @param array      $options
     *
     * @return string
     */
    public function thumbnail($media, $format, $options = [])
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * @param int|string $media
     * @param string     $format
     *
     * @return string
     */
    public function path($media, $format)
    {
        return $this->getTwigExtension()->path($media, $format);
    }

    public function getNodeVisitors()
    {
        return $this->getTwigExtension()->getNodeVisitors();
    }

    public function getFilters()
    {
        return $this->getTwigExtension()->getFilters();
    }

    public function getTests()
    {
        return $this->getTwigExtension()->getTests();
    }

    public function getFunctions()
    {
        return $this->getTwigExtension()->getFunctions();
    }

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
        return [];
    }

    public function getAllowedProperties()
    {
        return [];
    }
}
