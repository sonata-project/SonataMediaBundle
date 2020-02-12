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

use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Extra\ApiMediaFile;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Mime\MimeTypes;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @final since sonata-project/media-bundle 3.21.0
 */
class FileProvider extends BaseProvider implements FileProviderInterface
{
    protected $allowedExtensions;

    protected $allowedMimeTypes;

    protected $metadata;

    /**
     * @param string                   $name
     * @param MetadataBuilderInterface $metadata
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail, array $allowedExtensions = [], array $allowedMimeTypes = [], MetadataBuilderInterface $metadata = null)
    {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);

        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->metadata = $metadata;
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
            ['class' => 'fa fa-file-text-o']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceImage(MediaInterface $media)
    {
        return sprintf(
            '%s/%s',
            $this->generatePath($media),
            $media->getProviderReference()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceFile(MediaInterface $media)
    {
        return $this->getFilesystem()->get($this->getReferenceImage($media), true);
    }

    /**
     * @return string[]
     */
    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper)
    {
        $formMapper->add('name');
        $formMapper->add('enabled', null, ['required' => false]);
        $formMapper->add('authorName');
        $formMapper->add('cdnIsFlushable');
        $formMapper->add('description');
        $formMapper->add('copyright');
        $formMapper->add('binaryContent', FileType::class, ['required' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildCreateForm(FormMapper $formMapper)
    {
        $formMapper->add('binaryContent', FileType::class, [
            'constraints' => [
                new NotBlank(),
                new NotNull(),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildMediaType(FormBuilder $formBuilder)
    {
        if ('api' === $formBuilder->getOption('context')) {
            $formBuilder->add('binaryContent', FileType::class);
            $formBuilder->add('contentType');
        } else {
            $formBuilder->add('binaryContent', FileType::class, [
                'required' => false,
                'label' => 'widget_label_binary_content',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(MediaInterface $media)
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        $this->setFileContents($media);

        $this->generateThumbnails($media);

        $media->resetBinaryContent();
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(MediaInterface $media)
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        // Delete the current file from the FS
        $oldMedia = clone $media;
        // if no previous reference is provided, it prevents
        // Filesystem from trying to remove a directory
        if (null !== $media->getPreviousProviderReference()) {
            $oldMedia->setProviderReference($media->getPreviousProviderReference());

            $path = $this->getReferenceImage($oldMedia);

            if ($this->getFilesystem()->has($path)) {
                $this->getFilesystem()->delete($path);
            }
        }

        $this->fixBinaryContent($media);

        $this->setFileContents($media);

        $this->generateThumbnails($media);

        $media->resetBinaryContent();
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata(MediaInterface $media, $force = true)
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            // this is now optimized at all!!!
            $path = tempnam(sys_get_temp_dir(), 'sonata_update_metadata_');
            $fileObject = new \SplFileObject($path, 'w');
            $fileObject->fwrite($this->getReferenceFile($media)->getContent());
        } else {
            $fileObject = $media->getBinaryContent();
        }

        $media->setSize($fileObject->getSize());
    }

    /**
     * {@inheritdoc}
     */
    public function generatePublicUrl(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            $path = $this->getReferenceImage($media);
        } else {
            // @todo: fix the asset path
            $path = sprintf('sonatamedia/files/%s/file.png', $format);
        }

        return $this->getCdn()->getPath($path, $media->getCdnIsFlushable());
    }

    /**
     * {@inheritdoc}
     */
    public function getHelperProperties(MediaInterface $media, $format, $options = [])
    {
        return array_merge([
            'title' => $media->getName(),
            'thumbnail' => $this->getReferenceImage($media),
            'file' => $this->getReferenceImage($media),
        ], $options);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePrivateUrl(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $this->getReferenceImage($media);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = [])
    {
        // build the default headers
        $headers = array_merge([
            'Content-Type' => $media->getContentType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getMetadataValue('filename')),
        ], $headers);

        if (!\in_array($mode, ['http', 'X-Sendfile', 'X-Accel-Redirect'], true)) {
            throw new \RuntimeException('Invalid mode provided');
        }

        if ('http' === $mode) {
            if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
                $file = $this->getReferenceFile($media);
            } else {
                $file = $this->getFilesystem()->get($this->generatePrivateUrl($media, $format));
            }

            return new StreamedResponse(static function () use ($file) {
                echo $file->getContent();
            }, 200, $headers);
        }

        if (!$this->getFilesystem()->getAdapter() instanceof Local) {
            throw new \RuntimeException('Cannot use X-Sendfile or X-Accel-Redirect with non \Sonata\MediaBundle\Filesystem\Local');
        }

        $filename = sprintf(
            '%s/%s',
            $this->getFilesystem()->getAdapter()->getDirectory(),
            $this->generatePrivateUrl($media, $format)
        );

        return new BinaryFileResponse($filename, 200, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            return;
        }

        if ($media->getBinaryContent() instanceof UploadedFile) {
            $fileName = $media->getBinaryContent()->getClientOriginalName();
        } elseif ($media->getBinaryContent() instanceof File) {
            $fileName = $media->getBinaryContent()->getFilename();
        } else {
            throw new \RuntimeException(sprintf('Invalid binary content type: %s', \get_class($media->getBinaryContent())));
        }

        if ($media->getBinaryContent() instanceof UploadedFile && 0 === ($media->getBinaryContent()->getSize() ?: 0)) {
            $errorElement
               ->with('binaryContent')
                   ->addViolation('The file is too big, max size: '.ini_get('upload_max_filesize'))
               ->end();
        }

        if (!\in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->allowedExtensions, true)) {
            $errorElement
                ->with('binaryContent')
                ->addViolation('Invalid extensions')
                ->end();
        }

        if ('' !== $media->getBinaryContent()->getFilename() && !\in_array($media->getBinaryContent()->getMimeType(), $this->allowedMimeTypes, true)) {
            $errorElement
                ->with('binaryContent')
                    ->addViolation('Invalid mime type : %type%', ['%type%' => $media->getBinaryContent()->getMimeType()])
                ->end();
        }
    }

    protected function fixBinaryContent(MediaInterface $media)
    {
        if (null === $media->getBinaryContent() || $media->getBinaryContent() instanceof File) {
            return;
        }

        if ($media->getBinaryContent() instanceof Request) {
            $this->generateBinaryFromRequest($media);
            $this->updateMetadata($media);

            return;
        }

        // if the binary content is a filename => convert to a valid File
        if (!is_file($media->getBinaryContent())) {
            throw new \RuntimeException('The file does not exist : '.$media->getBinaryContent());
        }

        $binaryContent = new File($media->getBinaryContent());
        $media->setBinaryContent($binaryContent);
    }

    /**
     * @throws \RuntimeException
     */
    protected function fixFilename(MediaInterface $media)
    {
        if ($media->getBinaryContent() instanceof UploadedFile) {
            $media->setName($media->getName() ?: $media->getBinaryContent()->getClientOriginalName());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getClientOriginalName());
        } elseif ($media->getBinaryContent() instanceof File) {
            $media->setName($media->getName() ?: $media->getBinaryContent()->getBasename());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getBasename());
        }

        // this is the original name
        if (!$media->getName()) {
            throw new \RuntimeException('Please define a valid media\'s name');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doTransform(MediaInterface $media)
    {
        $this->fixBinaryContent($media);
        $this->fixFilename($media);

        if ($media->getBinaryContent() instanceof UploadedFile && 0 === $media->getBinaryContent()->getSize()) {
            $media->setProviderReference(uniqid($media->getName(), true));
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException('The uploaded file is not found');
        }

        // this is the name used to store the file
        if (!$media->getProviderReference() ||
            MediaInterface::MISSING_BINARY_REFERENCE === $media->getProviderReference()
        ) {
            $media->setProviderReference($this->generateReferenceName($media));
        }

        if ($media->getBinaryContent() instanceof File) {
            $media->setContentType($media->getBinaryContent()->getMimeType());
            $media->setSize($media->getBinaryContent()->getSize());
        }

        $media->setProviderStatus(MediaInterface::STATUS_OK);
    }

    /**
     * Set the file contents for an image.
     *
     * @param string $contents path to contents, defaults to MediaInterface BinaryContent
     */
    protected function setFileContents(MediaInterface $media, $contents = null)
    {
        $file = $this->getFilesystem()->get(sprintf('%s/%s', $this->generatePath($media), $media->getProviderReference()), true);
        $metadata = $this->metadata ? $this->metadata->get($media, $file->getName()) : [];

        if ($contents) {
            $file->setContent($contents, $metadata);

            return;
        }

        $binaryContent = $media->getBinaryContent();
        if ($binaryContent instanceof File) {
            $path = $binaryContent->getRealPath() ?: $binaryContent->getPathname();
            $file->setContent(file_get_contents($path), $metadata);

            return;
        }
    }

    /**
     * @return string
     */
    protected function generateReferenceName(MediaInterface $media)
    {
        return $this->generateMediaUniqId($media).'.'.$media->getBinaryContent()->guessExtension();
    }

    /**
     * @return string
     */
    protected function generateMediaUniqId(MediaInterface $media)
    {
        return sha1($media->getName().uniqid().random_int(11111, 99999));
    }

    /**
     * Set media binary content according to request content.
     */
    protected function generateBinaryFromRequest(MediaInterface $media)
    {
        if (!$media->getContentType()) {
            throw new \RuntimeException(
                'You must provide the content type value for your media before setting the binary content'
            );
        }

        $request = $media->getBinaryContent();

        if (!$request instanceof Request) {
            throw new \RuntimeException('Expected Request in binary content');
        }

        $content = $request->getContent();

        // create unique id for media reference
        $guesser = MimeTypes::getDefault();
        $extensions = $guesser->getExtensions($media->getContentType());
        $extension = $extensions[0] ?? null;

        if (!$extension) {
            throw new \RuntimeException(
                sprintf('Unable to guess extension for content type %s', $media->getContentType())
            );
        }

        $handle = tmpfile();
        fwrite($handle, $content);
        $file = new ApiMediaFile($handle);
        $file->setExtension($extension);
        $file->setMimetype($media->getContentType());

        $media->setBinaryContent($file);
    }
}
