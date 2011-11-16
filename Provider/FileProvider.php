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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class FileProvider extends BaseProvider
{
    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf('%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return \Gaufrette\File
     */
    public function getReferenceFile(MediaInterface $media)
    {
        return $this->getFilesystem()->get($this->getReferenceImage($media), true);
    }

    /**
     * Build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled');
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', 'file', array('required' => false));
    }

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', 'file');
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function postPersist(MediaInterface $media)
    {
        if ($media->getBinaryContent() === null) {
            return;
        }

        $this->setFileContents($media);

        $this->generateThumbnails($media);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function postUpdate(MediaInterface $media)
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        $this->fixBinaryContent($media);

        $this->setFileContents($media);

        $this->generateThumbnails($media);
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function fixBinaryContent(MediaInterface $media)
    {
        if ($media->getBinaryContent() === null) {
            return;
        }

        // if the binary content is a filename => convert to a valid File
        if (!$media->getBinaryContent() instanceof File) {
            if (!is_file($media->getBinaryContent())) {
                throw new \RuntimeException('The file does not exist : ' . $media->getBinaryContent());
            }

            $binaryContent = new File($media->getBinaryContent());

            $media->setBinaryContent($binaryContent);
        }
    }

    /**
     * @throws \RuntimeException
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    protected function fixFilename(MediaInterface $media)
    {
        if ($media->getBinaryContent() instanceof UploadedFile) {
            $media->setName($media->getBinaryContent()->getClientOriginalName());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getClientOriginalName());
        } else if ($media->getBinaryContent() instanceof File) {
            $media->setName($media->getBinaryContent()->getBasename());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getBasename());
        }

        // this is the original name
        if (!$media->getName()) {
            throw new \RuntimeException('Please define a valid media\'s name');
        }
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function prePersist(MediaInterface $media)
    {
        $this->fixBinaryContent($media);

        $media->setProviderName($this->name);
        $media->setProviderStatus(MediaInterface::STATUS_OK);

        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        $this->fixFilename($media);

        // this is the name used to store the file
        if (!$media->getProviderReference()) {
            $media->setProviderReference($this->generateReferenceName($media));
        }

        $media->setContentType($media->getBinaryContent()->getMimeType());
        $media->setSize($media->getBinaryContent()->getSize());
        $media->setCreatedAt(new \Datetime());
        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if ($format == 'reference') {
            $path = $this->getReferenceImage($media);
        } else {
            $path = sprintf('media_bundle/images/files/%s/file.png', $format);
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @param array $options
     * @return array
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = array())
    {
        return array_merge(array(
          'title'       => $media->getName(),
          'thumbnail'   => $this->getReferenceImage($media),
          'file'        => $this->getReferenceImage($media),
        ), $options);
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return bool
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        return false;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return
     */
    public function preUpdate(MediaInterface $media)
    {
        $this->fixBinaryContent($media);

        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        $this->fixFilename($media);

        // this is the name used to store the file
        if (!$media->getProviderReference()) {
            $media->setProviderReference($this->generateReferenceName($media));
        }

        $media->setContentType($media->getBinaryContent()->getMimeType());
        $media->setSize($media->getBinaryContent()->getSize());
        $media->setUpdatedAt(new \Datetime());
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preRemove(MediaInterface $media)
    {
        // never delete icon image
    }

    /**
     * Set the file contents for an image
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $contents path to contents, defaults to MediaInterface BinaryContent
     * @return void
     */
    public function setFileContents(MediaInterface $media, $contents=null)
    {
        $file = $this->getFilesystem()->get(
            sprintf('%s/%s', $this->generatePath($media), $media->getProviderReference()),
            true
        );
        if (!$contents) {
            $contents = $media->getBinaryContent()->getRealPath();
        }
        $file->setContent(file_get_contents($contents));
    }

    public function postRemove(MediaInterface $media)
    {
       // never delete icon image
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    protected function generateReferenceName(MediaInterface $media)
    {
        return sha1($media->getName() . rand(11111, 99999)) . '.' . $media->getBinaryContent()->guessExtension();
    }

    /**
     * Mode can be x-file
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param $format
     * @param $mode
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode = null)
    {
        // build the default headers
        $headers = array(
            'Content-Type'          => $media->getContentType(),
            'Content-Disposition'   => sprintf('attachment; filename="%s"', $media->getMetadataValue('filename')),
        );

        // create default variables
        if ($mode == 'X-Sendfile') {
            $headers['X-Sendfile'] = $this->generatePrivateUrl($media, $format);
            $content = '';
        } else if ($mode == 'X-Accel-Redirect') {
            $headers['X-Accel-Redirect'] = $this->generatePrivateUrl($media, $format);
            $content = '';
        } else if ($mode == 'http') {
            $content = $this->getReferenceFile($media)->getContent();
        } else {
            $content = '';
        }

        return new Response($content, 200, $headers);
    }
}