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

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class VimeoProvider extends BaseVideoProvider
{
    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = [])
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
            'allow_fullscreen' => isset($options['allowfullscreen']) ? true : false,
        ];

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata(
            $this->getName(),
            $this->getName().'.description',
            null,
            'SonataMediaBundle',
            ['class' => 'fa fa-vimeo-square']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        $url = sprintf('https://vimeo.com/api/oembed.json?url=%s', $this->getReferenceUrl($media));

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException $e) {
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

        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setLength($metadata['duration']);
        $media->setContentType('video/x-flv');
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = [])
    {
        return new RedirectResponse($this->getReferenceUrl($media), 302, $headers);
    }

    /**
     * Get provider reference url.
     *
     * @return string
     */
    public function getReferenceUrl(MediaInterface $media)
    {
        return sprintf('https://vimeo.com/%s', $media->getProviderReference());
    }

    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        if (preg_match("/vimeo\.com\/(video\/|)(\d+)/", $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches[2]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        $this->fixBinaryContent($media);

        if (!$media->getBinaryContent()) {
            return;
        }

        // store provider information
        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $this->updateMetadata($media, true);
    }
}
