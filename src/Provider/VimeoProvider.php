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

final class VimeoProvider extends BaseVideoProvider
{
    public function getHelperProperties(MediaInterface $media, string $format, array $options = []): array
    {
        // documentation : http://vimeo.com/api/docs/moogaloop
        $defaults = [
            // (optional) Flash Player version of app. Defaults to 9 .NEW!
            // 10 - New Moogaloop. 9 - Old Moogaloop without newest features.
            'fp_version' => 10,

            // (optional) Enable fullscreen capability. Defaults to true.
            'fullscreen' => true,

            // (optional) Show the byline on the video. Defaults to true.
            'title' => true,

            // (optional) Show the title on the video. Defaults to true.
            'byline' => 0,

            // (optional) Show the user's portrait on the video. Defaults to true.
            'portrait' => true,

            // (optional) Specify the color of the video controls.
            'color' => null,

            // (optional) Set to 1 to disable HD.
            'hd_off' => 0,

            // Set to 1 to enable the Javascript API.
            'js_api' => null,

            // (optional) JS function called when the player loads. Defaults to vimeo_player_loaded.
            'js_onLoad' => 0,

            // Unique id that is passed into all player events as the ending parameter.
            'js_swf_id' => uniqid('vimeo_player_'),
        ];

        $player_parameters = array_merge($defaults, $options['player_parameters'] ?? []);

        $box = $this->getBoxHelperProperties($media, $format, $options);

        $params = [
            'src' => http_build_query($player_parameters),
            'id' => $player_parameters['js_swf_id'],
            'frameborder' => $options['frameborder'] ?? 0,
            'width' => $box->getWidth(),
            'height' => $box->getHeight(),
            'class' => $options['class'] ?? '',
            'allow_fullscreen' => (bool) ($options['allowfullscreen'] ?? false),
        ];

        return $params;
    }

    public function getProviderMetadata(): MetadataInterface
    {
        return new Metadata(
            $this->getName(),
            $this->getName().'.description',
            null,
            'SonataMediaBundle',
            ['class' => 'fa fa-vimeo-square']
        );
    }

    public function updateMetadata(MediaInterface $media, bool $force = false): void
    {
        $url = \sprintf('https://vimeo.com/api/oembed.json?url=%s', $this->getReferenceUrl($media));

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException) {
            $media->setEnabled(false);
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            return;
        }

        // store provider information
        $media->setProviderMetadata($metadata);

        // update Media common fields from metadata
        if ($force) {
            $media->setName($metadata['title']);
            $media->setDescription($metadata['description']);
            $media->setAuthorName($metadata['author_name']);
        }

        $media->setHeight((int) $metadata['height']);
        $media->setWidth((int) $metadata['width']);
        $media->setLength((float) $metadata['duration']);
        $media->setContentType('video/x-flv');
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

        return \sprintf('https://vimeo.com/%s', $providerReference);
    }

    protected function fixBinaryContent(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        if (1 === preg_match('{vimeo\.com/(?:video/|)(?<video_id>\d+)}', $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches['video_id']);
        }
    }

    protected function doTransform(MediaInterface $media): void
    {
        $this->fixBinaryContent($media);

        if (null === $media->getBinaryContent()) {
            return;
        }

        // store provider information
        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->updateMetadata($media, true);
    }
}
