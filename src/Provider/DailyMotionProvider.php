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

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class DailyMotionProvider extends BaseVideoProvider
{
    public function getHelperProperties(MediaInterface $media, string $format, array $options = []): array
    {
        // documentation : http://www.dailymotion.com/en/doc/api/player

        $defaults = [
            // Values: 0 or 1. Default is 0. Determines if the player loads related videos when
            // the current video begins playback.
            'related' => 0,

            // Values: 0 or 1. Default is 1. Determines if the player allows explicit content to
            // be played. This parameter may be added to embed code by platforms which do not
            // want explicit content to be posted by their users.
            'explicit' => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will begin playing
            // automatically when the player loads.
            'autoPlay' => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will begin muted.
            'autoMute' => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will unmuted on mouse over.
            // Of course it works only if the player is on automute=1.
            'unmuteOnMouseOver' => 0,

            // Values: a number of seconds. Default is 0. Determines if the video will begin
            // playing the video at a given time.
            'start' => 0,

            // Values: 0 or 1. Default is 0. Enable the Javascript API by setting this parameter
            // to 1. For more information and instructions on using the Javascript API, see the
            // JavaScript API documentation.
            'enableApi' => 0,

            // Values: 0 or 1. Default is 0. Determines if the player should display controls
            // or not during video playback.
            'chromeless' => 0,

            // Values: 0 or 1. Default is 0. Determines if the video should be expended to fit
            // the whole player's size.
            'expendVideo' => 0,
            'color2' => null,

            // Player color changes may be set using color codes. A color is described by its
            // hexadecimal value (eg: FF0000 for red).
            'foreground' => null,
            'background' => null,
            'highlight' => null,
        ];

        $player_parameters = array_merge($defaults, $options['player_parameters'] ?? []);

        $box = $this->getBoxHelperProperties($media, $format, $options);

        $params = [
            'player_parameters' => http_build_query($player_parameters),
            'allowFullScreen' => $options['allowFullScreen'] ?? 'true',
            'allowScriptAccess' => $options['allowScriptAccess'] ?? 'always',
            'width' => $box->getWidth(),
            'height' => $box->getHeight(),
        ];

        return $params;
    }

    public function getProviderMetadata(): MetadataInterface
    {
        return new Metadata($this->getName(), $this->getName().'.description', 'bundles/sonatamedia/dailymotion-icon.png', 'SonataMediaBundle');
    }

    public function updateMetadata(MediaInterface $media, bool $force = false): void
    {
        $url = \sprintf('http://www.dailymotion.com/services/oembed?url=%s&format=json', $this->getReferenceUrl($media));

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException) {
            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        $media->setProviderMetadata($metadata);

        if ($force) {
            $media->setName($metadata['title']);
            $media->setAuthorName($metadata['author_name']);
        }

        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
    }

    public function getDownloadResponse(MediaInterface $media, string $format, string $mode, array $headers = []): Response
    {
        return new RedirectResponse($this->getReferenceUrl($media), 302, $headers);
    }

    public function getReferenceUrl(MediaInterface $media): string
    {
        $providerReference = $media->getProviderReference();

        if (null === $providerReference) {
            throw new \InvalidArgumentException('Unable to generate reference url for media without provider reference.');
        }

        return \sprintf('http://www.dailymotion.com/video/%s', $providerReference);
    }

    protected function doTransform(MediaInterface $media): void
    {
        $this->fixBinaryContent($media);

        if (null === $media->getBinaryContent()) {
            return;
        }

        $media->setProviderName($this->name);
        $media->setProviderStatus(MediaInterface::STATUS_OK);
        $media->setProviderReference($media->getBinaryContent());

        $this->updateMetadata($media, true);
    }

    private function fixBinaryContent(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        if (1 === preg_match('{^(?:https?://)?www.dailymotion.com/video/(?<video_id>[0-9a-zA-Z]*)}', $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches['video_id']);
        }
    }
}
