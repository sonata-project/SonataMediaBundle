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

use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\HttpFoundation\RedirectResponse;

class YouTubeProvider extends BaseVideoProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @param array $options
     * @return array
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        // documentation : http://code.google.com/apis/youtube/player_parameters.html

        $defaults = array(
            //Values: 0 or 1. Default is 1. Sets whether the player should load related
            // videos once playback of the initial video starts. Related videos are
            // displayed in the "genie menu" when the menu button is pressed. The player
            // search functionality will be disabled if rel is set to 0.
            'rel'       => 0,

            // Values: 0 or 1. Default is 0. Sets whether or not the initial video will autoplay
            // when the player loads.
            'autoplay'  => 0,

            // Values: 0 or 1. Default is 0. In the case of a single video player, a setting of 1
            // will cause the player to play the initial video again and again. In the case of a
            // playlist player (or custom player), the player will play the entire playlist and
            // then start again at the first video.
            'loop'      => 0,

            // Values: 0 or 1. Default is 0. Setting this to 1 will enable the Javascript API.
            // For more information on the Javascript API and how to use it, see the JavaScript
            // API documentation.
            'enablejsapi' => 0,

            // Value can be any alphanumeric string. This setting is used in conjunction with the
            // JavaScript API. See the JavaScript API documentation for details.
            'playerapiid' => null,

            // Values: 0 or 1. Default is 0. Setting to 1 will disable the player keyboard controls.
            // Keyboard controls are as follows:
            //      Spacebar: Play / Pause
            //      Arrow Left: Jump back 10% in the current video
            //      Arrow Right: Jump ahead 10% in the current video
            //      Arrow Up: Volume up
            //      Arrow Down: Volume Down
            'disablekb' => 0,

            // Values: 0 or 1. Default is 0. Setting to 1 enables the "Enhanced Genie Menu". This
            // behavior causes the genie menu (if present) to appear when the user's mouse enters
            // the video display area, as opposed to only appearing when the menu button is pressed.
            'egm' => 0,

            // Values: 0 or 1. Default is 0. Setting to 1 enables a border around the entire video
            // player. The border's primary color can be set via the color1 parameter, and a
            // secondary color can be set by the color2 parameter.
            'border' => 0,

            // Values: Any RGB value in hexadecimal format. color1 is the primary border color, and
            // color2 is the video control bar background color and secondary border color.
            'color1' => null,
            'color2' => null,

            // Values: A positive integer. This parameter causes the player to begin playing the video
            // at the given number of seconds from the start of the video. Note that similar to the
            // seekTo function, the player will look for the closest keyframe to the time you specify.
            // This means sometimes the play head may seek to just before the requested time, usually
            // no more than ~2 seconds
            'start' => 0,

            // Values: 0 or 1. Default is 0. Setting to 1 enables the fullscreen button. This has no
            // effect on the Chromeless Player. Note that you must include some extra arguments to
            // your embed code for this to work.
            'fs' => 1,

            // Values: 0 or 1. Default is 0. Setting to 1 enables HD playback by default. This has no
            // effect on the Chromeless Player. This also has no effect if an HD version of the video
            // is not available. If you enable this option, keep in mind that users with a slower
            // connection may have an sub-optimal experience unless they turn off HD. You should ensure
            // your player is large enough to display the video in its native resolution.
            'hd' => 1,

            // Values: 0 or 1. Default is 1. Setting to 0 disables the search box from displaying when
            // the video is minimized. Note that if the rel parameter is set to 0 then the search box
            // will also be disabled, regardless of the value of showsearch.
            'showsearch' => 0,

            // Values: 0 or 1. Default is 1. Setting to 0 causes the player to not display information
            // like the video title and rating before the video starts playing.
            'showinfo'  => 0,

            // Values: 1 or 3. Default is 1. Setting to 1 will cause video annotations to be shown by
            // default, whereas setting to 3 will cause video annotation to not be shown by default.
            'iv_load_policy' => 1,

            // Values: 1. Default is based on user preference. Setting to 1 will cause closed captions
            // to be shown by default, even if the user has turned captions off.
            'cc_load_policy' => 1
        );


        $player_parameters =  array_merge($defaults, isset($options['player_parameters']) ? $options['player_parameters'] : array());

        $params = array(
            'player_parameters' => http_build_query($player_parameters),
            'allowFullScreen'   => $player_parameters['fs'] == '1'      ? 'true' : 'false',
            'allowScriptAccess' => isset($options['allowScriptAccess']) ? $options['allowScriptAccess'] : 'always',
            'width'             => isset($options['width'])             ? $options['width']  : $media->getWidth(),
            'height'            => isset($options['height'])            ? $options['height'] : $media->getHeight(),
        );

        return $params;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function prePersist(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $metadata = $this->getMetadata($media);

        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);
        $media->setName($metadata['title']);
        $media->setAuthorName($metadata['author_name']);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setContentType('video/x-flv');
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return mixed|null|string
     */
    public function getMetadata(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return null;
        }

        $url = sprintf('http://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=%s&format=json', $media->getBinaryContent());
        $metadata = @file_get_contents($url);

        if (!$metadata) {
            throw new \RuntimeException('Unable to retrieve youtube video information for :' . $url);
        }

        $metadata = json_decode($metadata, true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode youtube video information for :' . $url);
        }

        return $metadata;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $format
     * @param $mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode = null)
    {
        return new RedirectResponse(sprintf('http://www.youtube.com/watch?v=%s', $media->getProviderReference()));
    }
}