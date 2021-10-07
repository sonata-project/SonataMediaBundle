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
use Twig\TwigFunction;

final class FormatterMediaExtension extends AbstractExtension
{
    /**
     * @var MediaExtension
     */
    private $twigExtension;

    /**
     * NEXT_MAJOR: Remove dependencies.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(MediaExtension $twigExtension)
    {
        $this->twigExtension = $twigExtension;
    }

    /**
     * NEXT_MAJOR: return empty array.
     *
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
                'getProviderReference',
            ],
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTokenParsers(): array
    {
        return [
            new MediaTokenParser(__CLASS__),
            new ThumbnailTokenParser(__CLASS__),
            new PathTokenParser(__CLASS__),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTwigExtension(): MediaExtension
    {
        return $this->twigExtension;
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
     */
    public function media($media, string $format, array $options = []): string
    {
        return $this->getTwigExtension()->media($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
     */
    public function thumbnail($media, string $format, array $options = []): string
    {
        return $this->getTwigExtension()->thumbnail($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int|string|MediaInterface|null $media
     */
    public function path($media, string $format): string
    {
        return $this->getTwigExtension()->path($media, $format);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getNodeVisitors(): array
    {
        return $this->getTwigExtension()->getNodeVisitors();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getFilters(): array
    {
        return $this->getTwigExtension()->getFilters();
    }

    /**
     * NEXT_MAJOR: Remove this method.
     */
    public function getTests(): array
    {
        return $this->getTwigExtension()->getTests();
    }

    public function getFunctions(): array
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
    public function getOperators(): array
    {
        return $this->getTwigExtension()->getOperators();
    }

    /**
     * @return string[]
     */
    public function getAllowedFilters(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAllowedFunctions(): array
    {
        return [
            'sonata_media',
            'sonata_thumbnail',
            'sonata_path',
        ];
    }

    /**
     * @return string[]
     */
    public function getAllowedProperties(): array
    {
        return [];
    }
}
