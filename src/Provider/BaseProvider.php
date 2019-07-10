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
use Sonata\CoreBundle\Validator\ErrorElement;
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

    /**
     * {@inheritdoc}
     */
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
        if ($media->getId() && $this->requireThumbnails() && !$media->getCdnIsFlushable()) {
            $flushPaths = [];
            foreach ($this->getFormats() as $format => $settings) {
                if (MediaProviderInterface::FORMAT_ADMIN === $format ||
                    substr($format, 0, \strlen((string) $media->getContext())) === $media->getContext()) {
                    $flushPaths[] = $this->getFilesystem()->get($this->generatePrivateUrl($media, $format), true)->getKey();
                }
            }
            if (!empty($flushPaths)) {
                $cdnFlushIdentifier = $this->getCdn()->flushPaths($flushPaths);
                $media->setCdnFlushIdentifier($cdnFlushIdentifier);
                $media->setCdnIsFlushable(true);
                $media->setCdnStatus(CDNInterface::STATUS_TO_FLUSH);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addFormat($name, $format)
    {
        $this->formats[$name] = $format;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat($name)
    {
        return isset($this->formats[$name]) ? $this->formats[$name] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function requireThumbnails()
    {
        return null !== $this->getResizer();
    }

    /**
     * {@inheritdoc}
     */
    public function generateThumbnails(MediaInterface $media)
    {
        $this->thumbnail->generate($this, $media);
    }

    /**
     * {@inheritdoc}
     */
    public function removeThumbnails(MediaInterface $media, $formats = null)
    {
        $this->thumbnail->delete($this, $media, $formats);
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return new Metadata($this->getName(), $this->getName().'.description', null, 'SonataMediaBundle', ['class' => 'fa fa-file']);
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(MediaInterface $media)
    {
        $hash = spl_object_hash($media);
        $this->clones[$hash] = clone $media;

        if ($this->requireThumbnails()) {
            $this->thumbnail->delete($this, $media);
        }
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function generatePath(MediaInterface $media)
    {
        return $this->pathGenerator->generatePath($media);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getResizer()
    {
        return $this->resizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdn()
    {
        return $this->cdn;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnPath($relativePath, $isFlushable)
    {
        return $this->getCdn()->getPath($relativePath, $isFlushable);
    }

    /**
     * {@inheritdoc}
     */
    public function setResizer(ResizerInterface $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(MediaInterface $media)
    {
        $media->setCreatedAt(new \DateTime());
        $media->setUpdatedAt(new \DateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(MediaInterface $media)
    {
        $media->setUpdatedAt(new \DateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, MediaInterface $media)
    {
    }

    abstract protected function doTransform(MediaInterface $media);
}
