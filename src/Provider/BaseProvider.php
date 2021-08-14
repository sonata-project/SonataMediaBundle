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
use Sonata\MediaBundle\Thumbnail\ThumbnailInterface;

abstract class BaseProvider implements MediaProviderInterface
{
    /**
     * @var array
     */
    protected $formats = [];

    /**
     * @var string[]
     */
    protected $templates = [];

    /**
     * @var ResizerInterface
     */
    protected $resizer;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var GeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var CDNInterface
     */
    protected $cdn;

    /**
     * @var ThumbnailInterface
     */
    protected $thumbnail;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var MediaInterface[]
     */
    private $clones = [];

    /**
     * @param string $name
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator, ThumbnailInterface $thumbnail)
    {
        $this->name = $name;
        $this->filesystem = $filesystem;
        $this->cdn = $cdn;
        $this->pathGenerator = $pathGenerator;
        $this->thumbnail = $thumbnail;
    }

    final public function transform(MediaInterface $media): void
    {
        if (null === $media->getBinaryContent()) {
            return;
        }

        $this->doTransform($media);
        $this->flushCdn($media);
    }

    public function flushCdn(MediaInterface $media)
    {
        if (null === $media->getId() || !$media->getCdnIsFlushable()) {
            // If the medium is new or if it isn't marked as flushable, skip the CDN flush process.
            return;
        }

        // Check if the medium already has a pending CDN flush.
        if ($media->getCdnFlushIdentifier()) {
            $cdnStatus = $this->getCdn()->getFlushStatus($media->getCdnFlushIdentifier());
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
            if (MediaProviderInterface::FORMAT_ADMIN === $format ||
                substr($format, 0, \strlen((string) $media->getContext())) === $media->getContext()) {
                $flushPaths[] = $this->getFilesystem()->get($this->generatePrivateUrl($media, $format), true)->getKey();
            }
        }

        if ([] !== $flushPaths) {
            $cdnFlushIdentifier = $this->getCdn()->flushPaths($flushPaths);
            $media->setCdnFlushIdentifier($cdnFlushIdentifier);
            $media->setCdnStatus(CDNInterface::STATUS_TO_FLUSH);
        }
    }

    public function addFormat($name, $format)
    {
        $this->formats[$name] = $format;
    }

    public function getFormat($name)
    {
        return $this->formats[$name] ?? false;
    }

    public function requireThumbnails()
    {
        return null !== $this->getResizer();
    }

    public function generateThumbnails(MediaInterface $media)
    {
        $this->thumbnail->generate($this, $media);
    }

    public function removeThumbnails(MediaInterface $media, $formats = null)
    {
        $this->thumbnail->delete($this, $media, $formats);
    }

    public function getFormatName(MediaInterface $media, $format)
    {
        if (MediaProviderInterface::FORMAT_ADMIN === $format) {
            return MediaProviderInterface::FORMAT_ADMIN;
        }

        if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
            return MediaProviderInterface::FORMAT_REFERENCE;
        }

        $baseName = $media->getContext().'_';
        if (substr($format, 0, \strlen($baseName)) === $baseName) {
            return $format;
        }

        return $baseName.$format;
    }

    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', ['class' => 'fa fa-file']);
    }

    public function preRemove(MediaInterface $media)
    {
        $hash = spl_object_hash($media);
        $this->clones[$hash] = clone $media;

        if ($this->requireThumbnails()) {
            $this->thumbnail->delete($this, $media);
        }
    }

    public function postRemove(MediaInterface $media)
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

    public function generatePath(MediaInterface $media)
    {
        return $this->pathGenerator->generatePath($media);
    }

    public function getFormats()
    {
        return $this->formats;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getTemplate($name)
    {
        return $this->templates[$name] ?? null;
    }

    public function getResizer()
    {
        return $this->resizer;
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function getCdn()
    {
        return $this->cdn;
    }

    public function getCdnPath($relativePath, $isFlushable)
    {
        return $this->getCdn()->getPath($relativePath, $isFlushable);
    }

    public function setResizer(ResizerInterface $resizer)
    {
        $this->resizer = $resizer;
    }

    public function prePersist(MediaInterface $media)
    {
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
    }

    public function preUpdate(MediaInterface $media)
    {
        $media->setUpdatedAt(new \DateTime());
    }

    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
    }

    abstract protected function doTransform(MediaInterface $media);
}
