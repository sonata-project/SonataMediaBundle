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
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Component\Form\FormBuilder;

interface MediaProviderInterface
{
    /**
     * @param string $name
     * @param array $format
     *
     * @return void
     */
    function addFormat($name, $format);

    /**
     * return the format settings
     *
     * @param string $name
     *
     * @return array|false the format settings
     */
    function getFormat($name);

    /**
     * return true if the media related to the provider required thumbnails (generation)
     *
     * @return boolean
     */
    function requireThumbnails();

    /**
     * generated thumbnails linked to the media, a thumbnail is a format used on the website
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function generateThumbnails(MediaInterface $media);

    /**
     * remove all linked thumbnails
     *
     * @param MediaInterface $media
     * @return void
     */
    function removeThumbnails(MediaInterface $media);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return \Gaufrette\File
     */
    function getReferenceFile(MediaInterface $media);

    /**
     * return the correct format name : providerName_format
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    function getFormatName(MediaInterface $media, $format);

    /**
     * return the reference image of the media, can be the video thumbnail or the original uploaded picture
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string to the reference image
     */
    function getReferenceImage(MediaInterface $media);

    /**
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function preUpdate(MediaInterface $media);

    /**
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function postUpdate(MediaInterface $media);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function preRemove(MediaInterface $media);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function postRemove(MediaInterface $media);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    function buildCreateForm(FormMapper $formMapper);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    function buildEditForm(FormMapper $formMapper);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function prePersist(MediaInterface $media);

    /**
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function postPersist(MediaInterface $media);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     */
    function getHelperProperties(MediaInterface $media, $format);

    /**
     * Generate the media path
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    function generatePath(MediaInterface $media);

    /**
     * Generate the public path
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    function generatePublicUrl(MediaInterface $media, $format);

    /**
     * Generate the private path
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    function generatePrivateUrl(MediaInterface $media, $format);

    /**
     *
     * @return array
     */
    function getFormats();

    /**
     *
     * @param string $name
     */
    function setName($name);

    /**
     * @return string
     */
    function getName();

    /**
     *
     * @param array $templates
     */
    function setTemplates(array $templates);
    /**
     *
     * @return array
     */
    function getTemplates();

    /**
     * @param string $name
     * @return void
     */
    function getTemplate($name);

    /**
     * Mode can be x-file
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $format
     * @param $mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    function getDownloadResponse(MediaInterface $media, $format, $mode);

    /**
     * @return \Sonata\MediaBundle\Media\ResizerInterface
     */
    function getResizer();

    /**
     * @return \Gaufrette\Filesystem
     */
    function getFilesystem();

    /**
     * @param string $relativePath
     * @param bool $isFlushable
     */
    function getCdnPath($relativePath, $isFlushable);

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function transform(MediaInterface $media);

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    function validate(ErrorElement $errorElement, MediaInterface $media);

    /**
     * @param \Symfony\Component\Form\FormBuilder $formBuilder
     * @return void
     */
    function buildMediaType(FormBuilder $formBuilder);
}