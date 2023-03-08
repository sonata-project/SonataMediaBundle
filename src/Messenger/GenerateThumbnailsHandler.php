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
use Sonata\MediaBundle\Thumbnail\GenerableThumbnailInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * @author Jordi Sala Morales <jordism91@gmail.com>
 *
 * @psalm-suppress DeprecatedInterface
 */
final class GenerateThumbnailsHandler implements MessageHandlerInterface
{
    public function __construct(
        private GenerableThumbnailInterface $thumbnail,
        private MediaManagerInterface $mediaManager,
        private Pool $pool
    ) {
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
        } catch (\Exception) {
            throw new UnrecoverableMessageHandlingException(sprintf('Provider "%s" not found.', $providerName));
        }

        $this->thumbnail->generate($provider, $media);
    }
}
