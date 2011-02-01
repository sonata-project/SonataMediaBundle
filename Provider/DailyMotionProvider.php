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


use Sonata\MediaBundle\Entity\BaseMedia as Media;

class DailyMotionProvider extends BaseProvider
{

    public function getReferenceImage(Media $media)
    {

        return $media->getMetadataValue('thumbnail_url');
    }

    public function getAbsolutePath(Media $media)
    {

        return sprintf('http://www.dailymotion.com/swf/video/%s', $media->getProviderReference());
    }


    /**
     * build the related create form
     *
     */
    function buildEditForm($form)
    {
        $form->add(new \Symfony\Component\Form\TextField('name'));
        $form->add(new \Symfony\Component\Form\CheckboxField('enabled'));
        $form->add(new \Symfony\Component\Form\TextField('author_name'));
        $form->add(new \Symfony\Component\Form\CheckboxField('cdn_is_flushable'));
        $form->add(new \Symfony\Component\Form\TextareaField('description'));
        $form->add(new \Symfony\Component\Form\TextField('copyright'));

        $form->add(new \Symfony\Component\Form\TextField('binary_content'));

    }

    /**
     * build the related create form
     *
     */
    function buildCreateForm($form)
    {
        $form->add(new \Symfony\Component\Form\TextField('binary_content'));
    }

    public function getHelperProperties(Media $media, $format, $options = array())
    {

        // documentation : http://www.dailymotion.com/en/doc/api/player

        $defaults = array(
            // Values: 0 or 1. Default is 0. Determines if the player loads related videos when
            // the current video begins playback.
            'related'   => 0,

            // Values: 0 or 1. Default is 1. Determines if the player allows explicit content to
            // be played. This parameter may be added to embed code by platforms which do not
            // want explicit content to be posted by their users.
            'explicit'  => 0,

            // Values: 0 or 1. Default is 0. Determines if the video will begin playing
            // automatically when the player loads.
            'autoPlay'      => 0,

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
        );


        $player_parameters =  array_merge($defaults, isset($options['player_parameters']) ? $options['player_parameters'] : array());

        $params = array(
            'player_parameters' => http_build_query($player_parameters),
            'allowFullScreen'   => isset($options['allowFullScreen'])   ? $options['allowFullScreen']     : 'true',
            'allowScriptAccess' => isset($options['allowScriptAccess']) ? $options['allowScriptAccess'] : 'always',
            'width'             => isset($options['width'])             ? $options['width']  : $media->getWidth(),
            'height'            => isset($options['height'])            ? $options['height'] : $media->getHeight(),
        );

        return $params;
    }

    public function prePersist(Media $media)
    {

        if (!$media->getBinaryContent()) {

            return;
        }

        $metadata = $this->getMetadata($media);
        
        $media->setProviderName($this->name);
        $media->setProviderMetadata($metadata);
        $media->setProviderReference($media->getBinaryContent());
        $media->setName($metadata['title']);
        $media->setAuthorName($metadata['author_name']);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setContentType('video/x-flv');
        $media->setProviderStatus(Media::STATUS_OK);

        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());

    }

    public function preUpdate(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $metadata = $this->getMetadata($media);

        $media->setProviderMetadata($metadata);
        $media->setProviderReference($media->getBinaryContent());
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setProviderStatus(Media::STATUS_OK);
        
        $media->setUpdatedAt(new \Datetime());
    }


    public function getMetadata($media)
    {

        if (!$media->getBinaryContent()) {

            return;
        }

        $url = sprintf('http://www.dailymotion.com/services/oembed?url=http://www.dailymotion.com/video/%s&format=json', $media->getBinaryContent());
        $metadata = @file_get_contents($url);

        if (!$metadata) {
            throw new \RuntimeException('Unable to retrieve dailymotion video information for :' . $url);
        }

        $metadata = json_decode($metadata, true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode dailymotion video information for :' . $url);
        }

        return $metadata;
    }

    public function postRemove(Media $media)
    {
        $files = array(
            $this->getReferenceImage($media),
        );

        foreach ($this->formats as $format => $definition) {
            $files[] = $this->generatePrivateUrl($media, $format);
        }


        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function postUpdate(Media $media)
    {
        $this->postPersist($media);
    }

    public function postPersist(Media $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $this->generateThumbnails($media);
    }

    public function preRemove(Media $media)
    {

    }
}