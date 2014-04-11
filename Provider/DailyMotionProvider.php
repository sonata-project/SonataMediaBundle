<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Sonata\CoreBundle\Model\Metadata;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DailyMotionProvider extends BaseVideoProvider
{
    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        // documentation : http://www.dailymotion.com/en/doc/api/player

        $defaults = array(
            // Values: 0 or 1. Default is 0. Determines if the player loads related videos when
            // the current video begins playback.
            'related'           => 0,

            // Values: 0 or 1. Default is 1. Determines if the player allows explicit content to
            // be played. This parameter may be added to embed code by platforms which do not
            // want explicit content to be posted by their users.
            'explicit'          => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will begin playing
            // automatically when the player loads.
            'autoPlay'          => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will begin muted.
            'autoMute'          => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will unmuted on mouse over.
            // Of course it works only if the player is on automute=1.
            'unmuteOnMouseOver' => 0,

            // Values: a number of seconds. Default is 0. Determines if the video will begin
            // playing the video at a given time.
            'start'             => 0,

            // Values: 0 or 1. Default is 0. Enable the Javascript API by setting this parameter
            // to 1. For more information and instructions on using the Javascript API, see the
            // JavaScript API documentation.
            'enableApi'         => 0,

            // Values: 0 or 1. Default is 0. Determines if the player should display controls
            // or not during video playback.
            'chromeless'        => 0,

            // Values: 0 or 1. Default is 0. Determines if the video should be expended to fit
            // the whole player's size.
            'expendVideo'       => 0,
            'color2'            => null,

            // Player color changes may be set using color codes. A color is described by its
            // hexadecimal value (eg: FF0000 for red).
            'foreground'        => null,
            'background'        => null,
            'highlight'         => null,
        );

        $player_parameters = array_merge($defaults, isset($options['player_parameters']) ? $options['player_parameters'] : array());

        $box = $this->getBoxHelperProperties($media, $format, $options);

        $params = array(
            'player_parameters' => http_build_query($player_parameters),
            'allowFullScreen'   => isset($options['allowFullScreen']) ? $options['allowFullScreen'] : 'true',
            'allowScriptAccess' => isset($options['allowScriptAccess']) ? $options['allowScriptAccess'] : 'always',
            'width'             => $box->getWidth(),
            'height'            => $box->getHeight(),
        );

        return $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().".description", "bundles/sonatamedia/dailymotion-icon.png", "SonataMediaBundle");
    }

    /**
     * {@inheritdoc}
     */
    protected function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        if (preg_match("/www.dailymotion.com\/video\/([0-9a-zA-Z]*)_/", $media->getBinaryContent(), $matches)) {
            $media->setBinaryContent($matches[1]);
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

        $media->setProviderName($this->name);
        $media->setProviderStatus(MediaInterface::STATUS_OK);
        $media->setProviderReference($media->getBinaryContent());

        $this->updateMetadata($media, true);
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = false)
    {
        $url = sprintf('http://www.dailymotion.com/services/oembed?url=http://www.dailymotion.com/video/%s&format=json', $media->getProviderReference());

        try {
            $metadata = $this->getMetadata($media, $url);
        } catch (\RuntimeException $e) {
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

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array())
    {
        return new RedirectResponse(sprintf('http://www.dailymotion.com/video/%s', $media->getProviderReference()), 302, $headers);
    }
}
