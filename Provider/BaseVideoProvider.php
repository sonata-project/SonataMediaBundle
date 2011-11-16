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

use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Media\ResizerInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;

abstract class BaseVideoProvider extends BaseProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return $media->getMetadataValue('thumbnail_url');
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return \Gaufrette\File
     */
    public function getReferenceFile(MediaInterface $media)
    {
        $key = $this->generatePrivateUrl($media, 'reference');

        // the reference file is remote, get it and store it with the 'reference' format
        if ($this->getFilesystem()->has($key)) {
            $referenceFile = $this->getFilesystem()->get($key);
        } else {
            $referenceFile = $this->getFilesystem()->get($key, true);
            $referenceFile->setContent(file_get_contents($this->getReferenceImage($media)));
        }

        return $referenceFile;
    }

      /**
     * Generate the public directory path (client side)
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        return $this->getCdn()->getPath(sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        ), $media->getCdnIsFlushable());
    }

    /**
     * Generate the private directory path (server side)
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return sprintf('%s/thumb_%d_%s.jpg',
            $this->generatePath($media),
            $media->getId(),
            $format
        );
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function preUpdate(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $metadata = $this->getMetadata($media);

        $media->setProviderReference($media->getBinaryContent());
        $media->setProviderMetadata($metadata);
        $media->setHeight($metadata['height']);
        $media->setWidth($metadata['width']);
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        $media->setUpdatedAt(new \Datetime());
    }


    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', 'text', array('required' => false));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     * @return void
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', 'text');
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function postUpdate(MediaInterface $media)
    {
        $this->postPersist($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function postPersist(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $this->generateThumbnails($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function postRemove(MediaInterface $media)
    {
    }
}
