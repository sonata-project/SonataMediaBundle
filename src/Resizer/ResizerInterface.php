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

namespace Sonata\MediaBundle\Resizer;

use Gaufrette\File;
use Imagine\Image\Box;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

/**
 * @phpstan-import-type FormatOptions from MediaProviderInterface
 */
interface ResizerInterface
{
    /**
     * @param array<string, int|string|bool|array|null>|false $settings
     *
     * @phpstan-param FormatOptions $settings
     */
    public function resize(MediaInterface $media, File $in, File $out, string $format, array $settings): void;

    /**
     * @param array<string, int|string|bool|array|null>|false $settings
     *
     * @phpstan-param FormatOptions $settings
     */
    public function getBox(MediaInterface $media, array $settings): Box;
}
