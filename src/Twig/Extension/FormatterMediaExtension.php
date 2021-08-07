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

final class FormatterMediaExtension extends AbstractExtension
{
    /**
     * @var MediaExtension
     */
    private $twigExtension;

    public function __construct(MediaExtension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * @return string[]
     */
    public function getAllowedTags(): array
    {
        return [
            'media',
            'path',
            'thumbnail',
        ];
    }

    /**
     * @return array<string, string[]>
     *
     * @phpstan-return array<class-string, string[]>
     */
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
     * @param int|string           $media
     * @param array<string, mixed> $options
     */
    public function media($media, string $format, array $options = []): string
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * @param int|string           $media
     * @param array<string, mixed> $options
     */
    public function thumbnail($media, string $format, array $options = []): string
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * @param int|string $media
     */
    public function path($media, string $format): string
    {
        return $this->getTwigExtension()->path($media, $format);
    }

    public function getNodeVisitors(): array
    {
        return $this->getTwigExtension()->getNodeVisitors();
    }

    public function getFilters(): array
    {
        return $this->getTwigExtension()->getFilters();
    }

    public function getTests(): array
    {
        return $this->getTwigExtension()->getTests();
    }

    public function getFunctions(): array
    {
        return $this->getTwigExtension()->getFunctions();
    }

    public function getOperators(): array
    {
        return $this->getTwigExtension()->getOperators();
    }
}
