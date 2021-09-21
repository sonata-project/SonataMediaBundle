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
 */
final class GenerateThumbnailsMessage
{
    /**
     * @var int|string|null
     */
    private $mediaId;

    /**
     * @param int|string|null $mediaId
     */
    public function __construct($mediaId)
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return int|string|null
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }
}
