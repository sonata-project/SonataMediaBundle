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

namespace Sonata\MediaBundle\Provider;

/**
 * @author Jordi Sala <jordism91@gmail.com>
 *
 * @phpstan-import-type FormatOptions from MediaProviderInterface
 */
interface ImageProviderInterface
{
    /**
     * @return array<string, int|string|bool|array|null>[]
     *
     * @phpstan-return FormatOptions[]
     */
    public function getFormatsForContext(string $context): array;
}
