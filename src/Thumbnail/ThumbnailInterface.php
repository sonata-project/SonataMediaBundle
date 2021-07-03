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

namespace Sonata\MediaBundle\Thumbnail;

use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;

interface ThumbnailInterface
{
    public function generatePublicUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string;

    public function generatePrivateUrl(MediaProviderInterface $provider, MediaInterface $media, string $format): string;

    public function generate(MediaProviderInterface $provider, MediaInterface $media): void;

    /**
     * @param string|string[] $formats
     */
    public function delete(MediaProviderInterface $provider, MediaInterface $media, $formats = null): void;
}
