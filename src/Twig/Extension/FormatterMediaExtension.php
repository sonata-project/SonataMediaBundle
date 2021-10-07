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
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class FormatterMediaExtension extends AbstractExtension
{
    /**
     * @return string[]
     */
    public function getAllowedTags(): array
    {
        return [];
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_media', [MediaRuntime::class, 'media'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_thumbnail', [MediaRuntime::class, 'thumbnail'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_path', [MediaRuntime::class, 'path']),
        ];
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
