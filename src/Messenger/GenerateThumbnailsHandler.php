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

use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Jordi Sala Morales <jordism91@gmail.com>
 */
final class GenerateThumbnailsHandler implements MessageHandlerInterface
{
    /**
     * @var ThumbnailInterface
     */
    private $thumbnail;

    /**
     * @var MediaManagerInterface
     */
    private $mediaManager;

    /**
     * @var Pool
     */
    private $pool;

    public function __construct(
        ThumbnailInterface $thumbnail,
        MediaManagerInterface $mediaManager,
        Pool $pool
    ) {
        $this->thumbnail = $thumbnail;
        $this->mediaManager = $mediaManager;
        $this->pool = $pool;
    }

    public function __invoke(GenerateThumbnailsMessage $message): void
    {
        $mediaId = $message->getMediaId();
        $media = $this->mediaManager->find($mediaId);

        if (null === $media) {
            throw new UnrecoverableMessageHandlingException(sprintf('Media "%s" not found.', $mediaId));
        }

        $providerName = $media->getProviderName();

        if (null === $providerName) {
            throw new UnrecoverableMessageHandlingException(sprintf('Media "%s" does not have a provider name.', $mediaId));
        }

        try {
            $provider = $this->pool->getProvider($providerName);
        } catch (\Exception $exception) {
            throw new UnrecoverableMessageHandlingException(sprintf('Provider "%s" not found.', $providerName));
        }

        $this->thumbnail->generate($provider, $media);
    }
}
