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
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\CDN\CDNInterface;
use Sonata\MediaBundle\Generator\GeneratorInterface;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Resizer\ResizerInterface;
use Sonata\MediaBundle\Thumbnail\GenerableThumbnailInterface;
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;

/**
 * @phpstan-import-type FormatOptions from MediaProviderInterface
 */
abstract class BaseProvider implements MediaProviderInterface
{
    /**
     * @var array<string, array<string, int|string|bool|array|null>>
     *
     * @phpstan-var array<string, FormatOptions>
     */
    protected array $formats = [];

    /**
     * @var string[]
     */
    protected array $templates = [];

    protected ?ResizerInterface $resizer = null;

    /**
     * @var MediaInterface[]
     */
    private array $clones = [];

    public function __construct(
        protected string $name,
        protected Filesystem $filesystem,
        protected CDNInterface $cdn,
        protected GeneratorInterface $pathGenerator,
        protected ThumbnailInterface $thumbnail
    ) {
    }

    final public function transform(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        $this->doTransform($media);
        $this->flushCdn($media);
    }

    final public function flushCdn(MediaInterface $media): void
    {
        if (null === $media->getId() || !$media->getCdnIsFlushable()) {
            // If the medium is new or if it isn't marked as flushable, skip the CDN flush process.
            return;
        }

        $flushIdentifier = $media->getCdnFlushIdentifier();

        // Check if the medium already has a pending CDN flush.
        if (null !== $flushIdentifier) {
            $cdnStatus = $this->getCdn()->getFlushStatus($flushIdentifier);
            // Update the flush status.
            $media->setCdnStatus($cdnStatus);

            if (!\in_array($cdnStatus, [CDNInterface::STATUS_OK, CDNInterface::STATUS_ERROR], true)) {
                // If the previous flush process is still pending, do nothing.
                return;
            }

            // If the previous flush process is finished, we clean its identifier.
            $media->setCdnFlushIdentifier(null);

            if (CDNInterface::STATUS_OK === $cdnStatus) {
                $media->setCdnFlushAt(new \DateTime());
            }
        }

        $flushPaths = [];

        foreach ($this->getFormats() as $format => $settings) {
            if (
                MediaProviderInterface::FORMAT_ADMIN === $format
                || substr($format, 0, \strlen($media->getContext() ?? '')) === $media->getContext()
            ) {
                $flushPaths[] = $this->getFilesystem()->get($this->generatePrivateUrl($media, $format), true)->getKey();
            }
        }

        if ([] !== $flushPaths) {
            $cdnFlushIdentifier = $this->getCdn()->flushPaths($flushPaths);
            $media->setCdnFlushIdentifier($cdnFlushIdentifier);
            $media->setCdnStatus(CDNInterface::STATUS_TO_FLUSH);
        }
    }

    final public function addFormat(string $name, array $settings): void
    {
        $this->formats[$name] = $settings;
    }

    final public function getFormat(string $name): array|false
    {
        return $this->formats[$name] ?? false;
    }

    final public function requireThumbnails(): bool
    {
        return null !== $this->getResizer();
    }

    final public function generateThumbnails(MediaInterface $media): void
    {
        if ($this->thumbnail instanceof GenerableThumbnailInterface) {
            $this->thumbnail->generate($this, $media);
        }
    }

    final public function removeThumbnails(MediaInterface $media, $formats = null): void
    {
        if ($this->thumbnail instanceof GenerableThumbnailInterface) {
            $this->thumbnail->delete($this, $media, $formats);
        }
    }

    final public function getFormatName(MediaInterface $media, string $format): string
    {
        if (MediaProviderInterface::FORMAT_ADMIN === $format) {
            return MediaProviderInterface::FORMAT_ADMIN;
        }

        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return MediaProviderInterface::FORMAT_REFERENCE;
        }

        $baseName = $media->getContext().'_';
        if (str_starts_with($format, $baseName)) {
            return $format;
        }

        return $baseName.$format;
    }

    public function getProviderMetadata(): MetadataInterface
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', ['class' => 'fa fa-file']);
    }

    public function preRemove(MediaInterface $media): void
    {
        $hash = spl_object_hash($media);
        $this->clones[$hash] = clone $media;

        if ($this->requireThumbnails()) {
            $this->removeThumbnails($media);
        }
    }

    public function postRemove(MediaInterface $media): void
    {
        $hash = spl_object_hash($media);

        if (isset($this->clones[$hash])) {
            $media = $this->clones[$hash];
            unset($this->clones[$hash]);
        }

        $path = $this->getReferenceImage($media);

        if ($this->getFilesystem()->has($path)) {
            $this->getFilesystem()->delete($path);
        }
    }

    final public function generatePath(MediaInterface $media): string
    {
        return $this->pathGenerator->generatePath($media);
    }

    final public function getFormats(): array
    {
        return $this->formats;
    }

    final public function setName(string $name): void
    {
        $this->name = $name;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    final public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }

    final public function getTemplates(): array
    {
        return $this->templates;
    }

    final public function getTemplate(string $name): ?string
    {
        return $this->templates[$name] ?? null;
    }

    final public function getResizer(): ?ResizerInterface
    {
        return $this->resizer;
    }

    final public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    final public function getCdn(): CDNInterface
    {
        return $this->cdn;
    }

    final public function getCdnPath(string $relativePath, bool $isFlushable = false): string
    {
        return $this->getCdn()->getPath($relativePath, $isFlushable);
    }

    final public function setResizer(ResizerInterface $resizer): void
    {
        $this->resizer = $resizer;
    }

    public function prePersist(MediaInterface $media): void
    {
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(MediaInterface $media): void
    {
        $media->setUpdatedAt(new \DateTime());
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media): void
    {
    }

    abstract protected function doTransform(MediaInterface $media): void;
}
