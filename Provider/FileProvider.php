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
    
class FileProvider extends BaseProvider
{

    public function getReferenceImage(MediaInterface $media)
    {

        return sprintf('%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    public function getAbsolutePath(MediaInterface $media)
    {

        return $this->getReferenceImage($media);
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

        $formMapper->add(new \Symfony\Component\Form\FileField('binaryContent', array(
            'secret' => 'file'
        )), array(), array(
            'type' => 'file'
        ));
    }

    /**
     * build the related create form
     *
     */
    function buildCreateForm(FormMapper $formMapper)
    {

        $formMapper->add(new \Symfony\Component\Form\FileField('binaryContent', array(
            'secret' => 'file'
        )), array(), array(
            'type' => 'file'
        ));
    }
    
    public function postPersist(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $file = $this->getFilesystem()->get(
            sprintf('%s/%s', $this->generatePath($media), $media->getProviderReference()),
            true
        );
        
        $file->setContent(file_get_contents($media->getBinaryContent()->getPath()));

        $this->generateThumbnails($media);
    }

    public function postUpdate(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        $this->fixBinaryContent($media);

        $file = $this->getFilesystem()->get(
            sprintf('%s/%s', $this->generatePath($media), $media->getProviderReference()),
            true
        );
        $file->setContent(file_get_contents($media->getBinaryContent()->getPath()));

        $this->generateThumbnails($media);
    }

    public function fixBinaryContent(MediaInterface $media)
    {
        if (!$media->getBinaryContent()) {
            return;
        }

        // if the binary content is a filename => convert to a valid File
        if (!$media->getBinaryContent() instanceof \Symfony\Component\HttpFoundation\File\File) {

            if (!is_file($media->getBinaryContent())) {
                throw new \RuntimeException('The file does not exist : ' . $media->getBinaryContent());
            }

            $binaryContent = new \Symfony\Component\HttpFoundation\File\File($media->getBinaryContent());

            $media->setBinaryContent($binaryContent);
        }
    }

    public function prePersist(MediaInterface $media)
    {

        $this->fixBinaryContent($media);

        $media->setProviderName($this->name);
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        if (!$media->getBinaryContent()) {

            return;
        }

        // this is the original name
        if (!$media->getName()) {
            $media->setName($media->getBinaryContent()->getName());
        }

        // this is the name used to store the file
        if (!$media->getProviderReference()) {
           $media->setProviderReference(sha1($media->getBinaryContent()->getName() . rand(11111, 99999)) . $media->getBinaryContent()->getExtension());
        }

        $media->setContentType($media->getBinaryContent()->getMimeType());
        $media->setSize($media->getBinaryContent()->getSize());

        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }


    public function generatePublicUrl(MediaInterface $media, $format)
    {

        // todo: add a valid icon set
        return $this->getCdn()->getPath(sprintf('media_bundle/images/files/%s/file.png', $format), $media->getCdnIsFlushable());
    }

    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        return array_merge(array(
          'title'       => $media->getName(),
          'thumbnail'   => $this->getReferenceImage($media),
          'file'        => $this->getReferenceImage($media),
        ), $options);
    }

    public function generatePrivateUrl(MediaInterface $media, $format)
    {

        return false;
    }

    public function preUpdate(MediaInterface $media)
    {

        $this->fixBinaryContent($media);
        
        if (!$media->getBinaryContent()) {

            return;
        }
                
        // this is the name used to store the file
        if (!$media->getProviderReference()) {
           $media->setProviderReference(sha1($media->getBinaryContent()->getName() . rand(11111, 99999)) . $media->getBinaryContent()->getExtension());
        }

        $media->setContentType($media->getBinaryContent()->getMimeType());
        $media->setSize($media->getBinaryContent()->getSize());
        $media->setUpdatedAt(new \Datetime());
    }

    public function preRemove(MediaInterface $media)
    {

    }
}