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
use Symfony\Component\Form\Form;
use Sonata\AdminBundle\Form\FormMapper;

class VimeoProvider extends BaseProvider
{

    /**
     * Return the reference image
     *
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return null
     */
    public function getReferenceImage(Media $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

    /**
     * return the absolute path of source media
     *
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return string
     */
    public function getAbsolutePath(Media $media)
    {
        return sprintf('http://www.vimeo.com/%s', $media->getProviderReference());
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @param string $format
     * @param array $options
     * @return array
     */
    public function getHelperProperties(Media $media, $format, $options = array())
    {

        // documentation : http://vimeo.com/api/docs/moogaloop
        $defaults = array(
            // (optional) Flash Player version of app. Defaults to 9 .NEW!
            // 10 - New Moogaloop. 9 - Old Moogaloop without newest features.
            'fp_version'      => 10,

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
        );

        $player_parameters =  array_merge($defaults, isset($options['player_parameters']) ? $options['player_parameters'] : array());

        $params = array(
            'src'         => http_build_query($player_parameters),
            'id'          => $player_parameters['js_swf_id'],
            'frameborder' => isset($options['frameborder']) ? $options['frameborder'] : 0,
            'width'       => isset($options['width'])             ? $options['width']  : $media->getWidth(),
            'height'      => isset($options['height'])            ? $options['height'] : $media->getHeight(),
        );

        return $params;
    }

    /**
     * build the related create form
     *
     */
    function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

    /**
     * build the related create form
     *
     */
    function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', array(), array('type' => 'string'));
    }

    /**
     *
     * @see BaseProvider::preSave
     */
    public function prePersist(Media $media)
    {

        if (!$media->getBinaryContent()) {

            return;
        }

        // retrieve metadata
        $metadata = $this->getMetadata($media);

        // store provider information
        $media->setProviderName($this->name);
        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);

        // update Media common field from metadata
        $media->setName($metadata['title']);
        $media->setDescription($metadata['description']);
        $media->setAuthorName($metadata['author_name']);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setLength($metadata['duration']);
        $media->setContentType('video/x-flv');
        $media->setProviderStatus(Media::STATUS_OK);

        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return 
     */
    public function preUpdate(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $metadata = $this->getMetadata($media);

        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setProviderStatus(Media::STATUS_OK);

        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return mixed|string
     */
    public function getMetadata(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $url = sprintf('http://vimeo.com/api/oembed.json?url=http://vimeo.com/%s', $media->getBinaryContent());
        $metadata = @file_get_contents($url);

        if (!$metadata) {
            throw new \RuntimeException('Unable to retrieve vimeo video information for :' . $url);
        }

        $metadata = json_decode($metadata, true);

        if (!$metadata) {
            throw new \RuntimeException('Unable to decode vimeo video information for :' . $url);
        }

        return $metadata;
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function postUpdate(Media $media)
    {
        $this->postPersist($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return
     */
    public function postPersist(Media $media)
    {
        if (!$media->getBinaryContent()) {

            return;
        }

        $this->generateThumbnails($media);
    }

    /**
     * @param \Sonata\MediaBundle\Entity\BaseMedia $media
     * @return void
     */
    public function preRemove(Media $media)
    {

    }

}