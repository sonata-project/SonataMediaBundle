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

abstract class BaseProvider implements MediaProviderInterface
{
    /**
     * @var array
     */
    protected $formats = array();

    protected $templates = array();

    protected $resizer;

    protected $filesystem;

    protected $pathGenerator;

    protected $cdn;

    /**
     * @param $name
     * @param \Gaufrette\Filesystem $filesystem
     * @param \Sonata\MediaBundle\CDN\CDNInterface $cdn
     * @param \Sonata\MediaBundle\Generator\GeneratorInterface $pathGenerator
     */
    public function __construct($name, Filesystem $filesystem, CDNInterface $cdn, GeneratorInterface $pathGenerator)
    {
        $this->name          = $name;
        $this->filesystem    = $filesystem;
        $this->cdn           = $cdn;
        $this->pathGenerator = $pathGenerator;
    }

    /**
     * @param string $name
     * @param array $format
     *
     * @return void
     */
    public function addFormat($name, $format)
    {
        $this->formats[$name] = $format;
    }

    /**
     * return the format settings
     *
     * @param string $name
     *
     * @return array|false the format settings
     */
    public function getFormat($name)
    {
        return isset($this->formats[$name]) ? $this->formats[$name] : false;
    }

    /**
     * return true if the media related to the provider required thumbnails (generation)
     *
     * @return boolean
     */
    public function requireThumbnails()
    {
        return $this->getResizer() !== null;
    }

    /**
     * generated thumbnails linked to the media, a thumbnail is a format used on the website
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param \Gaufrette\File $referenceFile
     * @param \Gaufrette\File $referenceFile
     * @return void
     */
    public function generateThumbnails(MediaInterface $media, File $referenceFile = null)
    {
        if (!$this->requireThumbnails()) {
            return;
        }

        $referenceFile = $referenceFile ? $referenceFile : $this->getReferenceFile($media);

        foreach ($this->formats as $format => $settings) {
            $this->getResizer()->resize(
                $media,
                $referenceFile,
                $this->getFilesystem()->get($this->generatePrivateUrl($media, $format), true),
                'jpg' ,
                $settings
            );
        }
    }

    /**
     * return the correct format name : providerName_format
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param string $format
     * @return string
     */
    public function getFormatName(MediaInterface $media, $format)
    {
        if ($format == 'admin') {
            return 'admin';
        }

        if ($format == 'reference') {
            return 'reference';
        }

        $baseName = $media->getContext().'_';
        if (substr($format, 0, strlen($baseName)) == $baseName) {
            return $format;
        }

        return $baseName.$format;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return void
     */
    public function preRemove(MediaInterface $media)
    {
        $path = $this->getReferenceImage($media);

        if ($this->getFilesystem()->has($path)) {
            $this->getFilesystem()->delete($path);
        }

        // delete the differents formats
        foreach ($this->formats as $format => $definition) {
            $path = $this->generatePrivateUrl($media, $format);
            if ($this->getFilesystem()->has($path)) {
                $this->getFilesystem()->delete($path);
            }
        }
    }

    /**
     * Generate the private path (client side)
     *
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @return string
     */
    public function generatePath(MediaInterface $media)
    {
        return $this->pathGenerator->generatePath($media);
    }

    /**
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param array $templates
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getTemplate($name)
    {
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
    }

    /**
     * @return \Sonata\MediaBundle\Media\ResizerInterface
     */
    public function getResizer()
    {
        return $this->resizer;
    }

    /**
     * @return \Gaufrette\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @return \Sonata\MediaBundle\CDN\CDNInterface
     */
    public function getCdn()
    {
        return $this->cdn;
    }

    /**
     * Return the Cdn base path
     *
     * @param string $relativePath
     * @param boolean $isFlushable
     *
     * @return string
     */
    public function getCdnPath($relativePath, $isFlushable)
    {
        return $this->getCdn()->getPath($relativePath, $isFlushable);
    }

    /**
     * @param \Sonata\MediaBundle\Media\ResizerInterface $resizer
     * @return void
     */
    public function setResizer(ResizerInterface $resizer)
    {
        $this->resizer = $resizer;
    }
}
