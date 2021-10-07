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

use Sonata\MediaBundle\Twig\MediaRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MediaExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('sonata_media', [MediaRuntime::class, 'media'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_thumbnail', [MediaRuntime::class, 'thumbnail'], ['is_safe' => ['html']]),
            new TwigFunction('sonata_path', [MediaRuntime::class, 'path']),
        ];
    }
}
