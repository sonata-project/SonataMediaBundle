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

use Gaufrette\File as GaufretteFile;
use Gaufrette\Filesystem;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class FileProvider extends BaseProvider implements FileProviderInterface
{
    /**
     * @param string[] $allowedExtensions
     * @param string[] $allowedMimeTypes
     */
    public function __construct(
        string $name,
        Filesystem $filesystem,
        CDNInterface $cdn,
        GeneratorInterface $pathGenerator,
        ThumbnailInterface $thumbnail,
        protected array $allowedExtensions = [],
        protected array $allowedMimeTypes = [],
        protected ?MetadataBuilderInterface $metadata = null
    ) {
        parent::__construct($name, $filesystem, $cdn, $pathGenerator, $thumbnail);
    }

    public function getProviderMetadata(): MetadataInterface
    {
        return new Metadata(
            $this->getName(),
            $this->getName().'.description',
            null,
            'SonataMediaBundle',
            ['class' => 'fa fa-file-text-o']
        );
    }

    public function getReferenceImage(MediaInterface $media): string
    {
        $providerReference = $media->getProviderReference();

        if (null === $providerReference) {
            throw new \InvalidArgumentException('Unable to generate reference image for media without provider reference.');
        }

        return \sprintf('%s/%s', $this->generatePath($media), $providerReference);
    }

    public function getReferenceFile(MediaInterface $media): GaufretteFile
    {
        return $this->getFilesystem()->get($this->getReferenceImage($media), true);
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    public function buildEditForm(FormMapper $form): void
    {
        $form->add('name');
        $form->add('enabled', null, ['required' => false]);
        $form->add('authorName');
        $form->add('cdnIsFlushable');
        $form->add('description');
        $form->add('copyright');
        $form->add('binaryContent', FileType::class, ['required' => false]);
    }

    public function buildCreateForm(FormMapper $form): void
    {
        $form->add('binaryContent', FileType::class, [
            'constraints' => [
                new NotBlank(),
                new NotNull(),
            ],
        ]);
    }

    public function buildMediaType(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add('binaryContent', FileType::class, [
            'required' => false,
            'label' => 'widget_label_binary_content',
        ]);
    }

    public function postPersist(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        $this->setFileContents($media);

        $this->generateThumbnails($media);

        $media->resetBinaryContent();
    }

    public function postUpdate(MediaInterface $media): void
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

    public function updateMetadata(MediaInterface $media, bool $force = true): void
    {
        if (!$media->getBinaryContent() instanceof \SplFileInfo) {
            // this is now optimized at all!!!
            $path = tempnam(sys_get_temp_dir(), 'sonata_update_metadata_');

            if (false === $path) {
                throw new \InvalidArgumentException(\sprintf('Unable to generate temporary file name for media %s.', $media->getId() ?? ''));
            }

            $fileObject = new \SplFileObject($path, 'w');
            $fileObject->fwrite($this->getReferenceFile($media)->getContent());
        } else {
            $fileObject = $media->getBinaryContent();
        }

        $media->setSize($fileObject->getSize());
    }

    public function generatePublicUrl(MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return $this->getCdn()->getPath($this->getReferenceImage($media), $media->getCdnIsFlushable());
        }

        return $this->thumbnail->generatePublicUrl($this, $media, $format);
    }

    public function getHelperProperties(MediaInterface $media, string $format, array $options = []): array
    {
        return array_merge([
            'title' => $media->getName(),
            'thumbnail' => $this->getReferenceImage($media),
            'file' => $this->getReferenceImage($media),
        ], $options);
    }

    public function generatePrivateUrl(MediaInterface $media, string $format): string
    {
        if (self::FORMAT_REFERENCE === $format) {
            return $this->getReferenceImage($media);
        }

        return $this->thumbnail->generatePrivateUrl($this, $media, $format);
    }

    public function getDownloadResponse(MediaInterface $media, string $format, string $mode, array $headers = []): Response
    {
        // build the default headers
        $headers = array_merge([
            'Content-Type' => $media->getContentType(),
            'Content-Disposition' => \sprintf('attachment; filename="%s"', $media->getMetadataValue('filename')),
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

            return new StreamedResponse(static function () use ($file): void {
                echo $file->getContent();
            }, 200, $headers);
        }

        $adapter = $this->getFilesystem()->getAdapter();

        if (!$adapter instanceof Local) {
            throw new \RuntimeException(\sprintf('Cannot use X-Sendfile or X-Accel-Redirect with non %s.', Local::class));
        }

        $directory = $adapter->getDirectory();

        if (false === $directory) {
            throw new \RuntimeException('Cannot retrieve directory from the adapter.');
        }

        return new BinaryFileResponse(
            \sprintf('%s/%s', $directory, $this->generatePrivateUrl($media, $format)),
            200,
            $headers
        );
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {
        $binaryContent = $media->getBinaryContent();

        if (!$binaryContent instanceof \SplFileInfo) {
            return;
        }

        if ($binaryContent instanceof UploadedFile) {
            $fileName = $binaryContent->getClientOriginalName();
        } elseif ($binaryContent instanceof File) {
            $fileName = $binaryContent->getFilename();
        } else {
            throw new \RuntimeException(\sprintf('Invalid binary content type: %s', $binaryContent::class));
        }

        if ($binaryContent instanceof UploadedFile && 0 === $binaryContent->getSize()) {
            $errorElement
                ->with('binaryContent')
                    ->addViolation(
                        'The file is too big, max size: %maxFileSize%',
                        ['%maxFileSize%' => \ini_get('upload_max_filesize')]
                    )
                ->end();
        }

        if (!\in_array(strtolower(pathinfo($fileName, \PATHINFO_EXTENSION)), $this->allowedExtensions, true)) {
            $errorElement
                ->with('binaryContent')
                    ->addViolation('Invalid extensions')
                ->end();
        }

        if (
            '' !== $media->getBinaryContent()->getFilename()
            && !\in_array(strtolower($media->getBinaryContent()->getMimeType()), $this->allowedMimeTypes, true)
        ) {
            $errorElement
                ->with('binaryContent')
                    ->addViolation('Invalid mime type : %type%', ['%type%' => $media->getBinaryContent()->getMimeType()])
                ->end();
        }
    }

    protected function fixBinaryContent(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent() || $media->getBinaryContent() instanceof File) {
            return;
        }

        // if the binary content is a filename => convert to a valid File
        if (!is_file($media->getBinaryContent())) {
            throw new \RuntimeException(\sprintf('The file does not exist: %s', $media->getBinaryContent()));
        }

        $binaryContent = new File($media->getBinaryContent());
        $media->setBinaryContent($binaryContent);
    }

    /**
     * @throws \RuntimeException
     */
    protected function fixFilename(MediaInterface $media): void
    {
        if ($media->getBinaryContent() instanceof UploadedFile) {
            $media->setName($media->getName() ?? $media->getBinaryContent()->getClientOriginalName());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getClientOriginalName());
        } elseif ($media->getBinaryContent() instanceof File) {
            $media->setName($media->getName() ?? $media->getBinaryContent()->getBasename());
            $media->setMetadataValue('filename', $media->getBinaryContent()->getBasename());
        }

        // This is the original name
        if (null === $media->getName()) {
            throw new \RuntimeException('Please define a valid media\'s name');
        }
    }

    protected function doTransform(MediaInterface $media): void
    {
        $this->fixBinaryContent($media);
        $this->fixFilename($media);

        if ($media->getBinaryContent() instanceof UploadedFile && 0 === $media->getBinaryContent()->getSize()) {
            $media->setProviderReference(uniqid($media->getName() ?? '', true));
            $media->setProviderStatus(MediaInterface::STATUS_ERROR);

            throw new UploadException('The uploaded file is not found');
        }

        // this is the name used to store the file
        if (null === $media->getProviderReference()
            || MediaInterface::MISSING_BINARY_REFERENCE === $media->getProviderReference()
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
     */
    protected function setFileContents(MediaInterface $media, ?string $contents = null): void
    {
        $providerReference = $media->getProviderReference();

        if (null === $providerReference) {
            throw new \RuntimeException(\sprintf(
                'Unable to generate path to file without provider reference for media "%s".',
                (string) $media
            ));
        }

        $file = $this->getFilesystem()->get(
            \sprintf('%s/%s', $this->generatePath($media), $providerReference),
            true
        );

        $metadata = null !== $this->metadata ? $this->metadata->get($media, $file->getName()) : [];

        if (null !== $contents) {
            $file->setContent($contents, $metadata);

            return;
        }

        $binaryContent = $media->getBinaryContent();
        if ($binaryContent instanceof File) {
            $path = false !== $binaryContent->getRealPath() ? $binaryContent->getRealPath() : $binaryContent->getPathname();
            $fileContents = file_get_contents($path);

            if (false === $fileContents) {
                throw new \RuntimeException(\sprintf('Unable to get file contents for media %s', $media->getId() ?? ''));
            }

            $file->setContent($fileContents, $metadata);

            return;
        }
    }

    protected function generateReferenceName(MediaInterface $media): string
    {
        return $this->generateMediaUniqId($media).'.'.$media->getBinaryContent()->guessExtension();
    }

    protected function generateMediaUniqId(MediaInterface $media): string
    {
        return sha1($media->getName().uniqid().random_int(11111, 99999));
    }
}
