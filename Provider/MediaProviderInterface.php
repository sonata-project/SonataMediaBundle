<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Provider;

use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\MetadataInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Symfony\Component\Form\FormBuilder;

interface MediaProviderInterface
{
    /**
     * @param string $name
     * @param array  $format
     */
    public function addFormat($name, $format);

    /**
     * return the format settings.
     *
     * @param string $name
     *
     * @return array|false the format settings
     */
    public function getFormat($name);

    /**
     * return true if the media related to the provider required thumbnails (generation).
     *
     * @return bool
     */
    public function requireThumbnails();

    /**
     * Generated thumbnails linked to the media, a thumbnail is a format used on the website.
     *
     * @param MediaInterface $media
     */
    public function generateThumbnails(MediaInterface $media);

    /**
     * remove linked thumbnails.
     *
     * @param MediaInterface $media
     * @param string|array   $formats
     */
    public function removeThumbnails(MediaInterface $media, $formats = null);

    /**
     * @param MediaInterface $media
     *
     * @return \Gaufrette\File
     */
    public function getReferenceFile(MediaInterface $media);

    /**
     * return the correct format name : providerName_format.
     *
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function getFormatName(MediaInterface $media, $format);

    /**
     * return the reference image of the media, can be the video thumbnail or the original uploaded picture.
     *
     * @param MediaInterface $media
     *
     * @return string to the reference image
     */
    public function getReferenceImage(MediaInterface $media);

    /**
     * @param MediaInterface $media
     */
    public function preUpdate(MediaInterface $media);

    /**
     * @param MediaInterface $media
     */
    public function postUpdate(MediaInterface $media);

    /**
     * @param MediaInterface $media
     */
    public function preRemove(MediaInterface $media);

    /**
     * @param MediaInterface $media
     */
    public function postRemove(MediaInterface $media);

    /**
     * build the related create form.
     *
     * @param FormMapper $formMapper
     */
    public function buildCreateForm(FormMapper $formMapper);

    /**
     * build the related create form.
     *
     * @param FormMapper $formMapper
     */
    public function buildEditForm(FormMapper $formMapper);

    /**
     * @param MediaInterface $media
     */
    public function prePersist(MediaInterface $media);

    /**
     * @param MediaInterface $media
     */
    public function postPersist(MediaInterface $media);

    /**
     * @param MediaInterface $media
     * @param string         $format
     * @param array          $options
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array());

    /**
     * Generate the media path.
     *
     * @param MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaInterface $media);

    /**
     * Generate the public path.
     *
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function generatePublicUrl(MediaInterface $media, $format);

    /**
     * Generate the private path.
     *
     * @param MediaInterface $media
     * @param string         $format
     *
     * @return string
     */
    public function generatePrivateUrl(MediaInterface $media, $format);

    /**
     * @return array
     */
    public function getFormats();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return MetadataInterface
     */
    public function getProviderMetadata();

    /**
     * @param array $templates
     */
    public function setTemplates(array $templates);

    /**
     * @return string[]
     */
    public function getTemplates();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getTemplate($name);

    /**
     * Mode can be x-file.
     *
     * @param MediaInterface $media
     * @param string         $format
     * @param string         $mode
     * @param array          $headers
     *
     * @return Response
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = array());

    /**
     * @return ResizerInterface
     */
    public function getResizer();

    /**
     * @return Filesystem
     */
    public function getFilesystem();

    /**
     * @param string $relativePath
     * @param bool   $isFlushable
     */
    public function getCdnPath($relativePath, $isFlushable);

    /**
     * @param MediaInterface $media
     */
    public function transform(MediaInterface $media);

    /**
     * @param ErrorElement   $errorElement
     * @param MediaInterface $media
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media);

    /**
     * @param FormBuilder $formBuilder
     */
    public function buildMediaType(FormBuilder $formBuilder);

    /**
     * @param MediaInterface $media
     * @param bool           $force
     */
    public function updateMetadata(MediaInterface $media, $force = false);
}
