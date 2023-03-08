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

namespace Sonata\MediaBundle\Messenger;

/**
 * @author Jordi Sala Morales <jordism91@gmail.com>
 *
 * @psalm-pure
 */
final class GenerateThumbnailsMessage
{
    public function __construct(private int|string $mediaId)
    {
    }

    public function getMediaId(): int|string
    {
        return $this->mediaId;
    }
}
