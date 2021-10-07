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
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\MediaRuntime;
use Sonata\MediaBundle\Twig\TokenParser\MediaTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\PathTokenParser;
use Sonata\MediaBundle\Twig\TokenParser\ThumbnailTokenParser;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MediaExtension extends AbstractExtension
{
    /**
     * @var Pool
     */
    private $pool;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * NEXT_MAJOR: Remove dependencies.
     *
     * @internal This class should only be used through Twig
     */
    public function __construct(Pool $pool, MediaManagerInterface $mediaManager, Environment $twig)
    {
        $this->pool = $pool;
        $this->mediaManager = $mediaManager;
        $this->twig = $twig;
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
    public function getTokenParsers(): array
    {
        return [
            new MediaTokenParser(static::class),
            new ThumbnailTokenParser(static::class),
            new PathTokenParser(static::class),
        ];
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
     */
    public function media($media, string $format, array $options = []): string
    {
        @trigger_error(
            'Render media through media twig tag is deprecated since sonata-project/media-bundle 3.34 and will be removed'
            .' in version 4.0. Use "sonata_media()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->pool,
            $this->mediaManager,
            $this->twig
        ))->media($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * Returns the thumbnail for the provided media.
     *
     * @param int|string|MediaInterface|null $media
     * @param array<string, mixed>           $options
     */
    public function thumbnail($media, string $format, array $options = []): string
    {
        @trigger_error(
            'Render media through thumbnail twig tag is deprecated since sonata-project/media-bundle 3.34 and will be removed'
            .' in version 4.0. Use "sonata_thumbnail()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->pool,
            $this->mediaManager,
            $this->twig
        ))->thumbnail($media, $format, $options);
    }

    /**
     * NEXT_MAJOR: Remove this method.
     *
     * @param int|string|MediaInterface|null $media
     */
    public function path($media, string $format): string
    {
        @trigger_error(
            'Render media through path twig tag is deprecated since sonata-project/media-bundle 3.34 and will be removed'
            .' in version 4.0. Use "sonata_path()" twig function instead.',
            \E_USER_DEPRECATED
        );

        return (new MediaRuntime(
            $this->pool,
            $this->mediaManager,
            $this->twig
        ))->path($media, $format);
    }
}
